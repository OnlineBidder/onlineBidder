<?php
namespace Socnet\Logic;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use getjump\Vk;
use Socnet\Entity\VkAds;
use Socnet\Entity\VkAdsLayout;
use Socnet\Entity\VkAdsRejectionReasons;
use Socnet\Entity\VkAdsTargeting;
use Socnet\Entity\VkCampaigns;
use Socnet\Entity\Cabinets;
use Socnet\Entity\VkAccounts;
use Socnet\Entity\VkClients;
use Socnet\Entity\VkDataUpdateQueue;
use Socnet\Entity\VkPriorityQueue;
use Zend\Db\ResultSet\ResultSet;
use Socnet\Entity\VkCountries;

class VkLogic implements ServiceLocatorAwareInterface
{
    const LOCK_ADS_STATISTIC = 1;
    const LOCK_ADS_INFORMATION = 2;
    const LOCK_CAMPAIGNS_INFORMATION = 3;
    const LOCK_ADS_LAYOUT = 4;
    const LOCK_ADS_TARGETING = 5;

    const ADS_ACTION_START = 1;
    const ADS_ACTION_STOP = 0;
    const ADS_ACTION_DELETE = 2;
    const ADS_ACTION_COPY = 3;
    const ADS_ACTION_SETTINGS = 4;

    const MAX_VK_EXECUTE_LIMIT = 25;

    const AD_STATUS_STOP = 0;
    const AD_STATUS_PLAY = 1;
    const AD_STATUS_DELETE = 2;

    const AD_APPROVED_NEW = 0;
    const AD_APPROVED_MODERATION = 1;
    const AD_APPROVED_APPROVE = 2;
    const AD_APPROVED_REJECT = 3;

    const ADS_MAX_GROUP_COUNT = 5;

    const CRON_CHECK_TABLE = 'cronCheck';

    /**
     * @var array [type => lockTimeInSeconds]
     */
    private $_lockConfig = [
        self::LOCK_ADS_STATISTIC => 60,
        self::LOCK_ADS_INFORMATION => 60,
        self::LOCK_CAMPAIGNS_INFORMATION => 350,
        self::LOCK_ADS_LAYOUT => 500,
        self::LOCK_ADS_TARGETING => 300,
    ];

    private $_methodConfig = [
        self::ADS_ACTION_STOP => 'ads.updateAds',
        self::ADS_ACTION_START => 'ads.updateAds',
        self::ADS_ACTION_SETTINGS => 'ads.updateAds',
        self::ADS_ACTION_DELETE => 'ads.deleteAds',
        self::ADS_ACTION_COPY => 'ads.createAds',
    ];

    /**
     * @var ServiceLocatorInterface
     */
    protected $_serviceLocator;
    protected $_objectManager;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->_serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->_serviceLocator;
    }

    public function getAdsStatistic()
    {
        if (!$this->isTimeForGetQuery(self::LOCK_ADS_STATISTIC)) {return false;}
        /**@var $vkAccount VkAccounts*/
        /**@var $vkCabinet Cabinets*/
        $vkAccounts = $this->getVkAccounts();
        foreach ($vkAccounts as $vkAccount) {
            $vkCabinets = $this->getVkCabinets($vkAccount);
            foreach ($vkCabinets as $vkCabinet) {
                $vkCampaigns = $this->getVkCampaigns($vkCabinet, $vkAccount);
                $vkCampaignsIds = $this->collectCampaignsIds($vkCampaigns);
                $vkAds = $this->getVkAds($vkCampaignsIds);
                $vkAdsIds = $this->collectAdsIds($vkAds);
                if (!empty($vkAdsIds)) {
                    $this->pushIntoQueue('ads.getStatistics', $vkAccount->getAccessKey(),
                        serialize(['account_id' => $vkCabinet->getAccountId(), 'ids_type' => 'ad', 'ids' => implode(',', $vkAdsIds), 'period' => 'day', 'date_from' => date('Y-m-d'), 'date_to' => 0]),
                        false
                    );
                }
            }
        }
        $this->getObjectManager()->flush();
        return true;
    }

    public function getAdsInformation()
    {
        $checkCampaigns = $getAdsLayout = $getTargeting = false;
        if (!$this->isTimeForGetQuery(self::LOCK_ADS_INFORMATION)) {return false;}
        if ($this->isTimeForGetQuery(self::LOCK_CAMPAIGNS_INFORMATION)) {$checkCampaigns = true;}
        if ($this->isTimeForGetQuery(self::LOCK_ADS_LAYOUT)) {$getAdsLayout = true;}
        if ($this->isTimeForGetQuery(self::LOCK_ADS_TARGETING)) {$getTargeting = true;}

        /**@var $vkAccount VkAccounts*/
        /**@var $vkCabinet Cabinets*/
        /**@var $vkClient VkClients*/

        $vkAccounts = $this->getVkAccounts();
        foreach ($vkAccounts as $vkAccount) {
            $vkCabinets = $this->getVkCabinets($vkAccount);
            foreach ($vkCabinets as $vkCabinet) {
                if ($vkCabinet->getAccountType() === 'agency') {
                    $vkClients = $this->getVkClients($vkAccount, $vkCabinet);
                    foreach ($vkClients as $vkClient) {
                        if ($checkCampaigns) {
                            $this->pushIntoQueue('ads.getCampaigns', $vkAccount->getAccessKey(),
                                serialize(['account_id' => $vkCabinet->getAccountId(), 'client_id' => $vkClient->getId(), 'include_deleted' => 1]),
                                false,
                                serialize(['vk_account_id' => $vkAccount->getId()])
                            );
                        }
                        if ($getAdsLayout) {
                            $this->pushIntoQueue('ads.getAdsLayout', $vkAccount->getAccessKey(),
                                serialize(['account_id' => $vkCabinet->getAccountId(), 'client_id' => $vkClient->getId()])
                            );
                        }
                        if ($getTargeting) {
                            $this->pushIntoQueue('ads.getAdsTargeting', $vkAccount->getAccessKey(),
                                serialize(['account_id' => $vkCabinet->getAccountId(), 'client_id' => $vkClient->getId()])
                            );
                        }
                        $this->pushIntoQueue('ads.getAds', $vkAccount->getAccessKey(),
                            serialize(['account_id' => $vkCabinet->getAccountId(), 'client_id' => $vkClient->getId(), 'include_deleted' => rand(0,1)])
                        );
                    }
                } else {

                }

            }
        }
        $this->getObjectManager()->flush();
        return true;
    }

    public function collateQueue($onlyPriority = false)
    {
        echo(' - get all queue' . PHP_EOL);
        $isPriority = true;
        $queue = $this->getVkPriorityQueue();
        if (!$onlyPriority && !$queue) {
            $isPriority = false;
            $queue = $this->getVkDataUpdateQueue();
        }

        /**@var $task VkDataUpdateQueue*/
        /**@var $vkAd VkAds*/
        /**@var $adObject VkAds*/
        foreach ($queue as $countIteration => $task) {
            echo('  -- queue number '.$countIteration . PHP_EOL);
            echo('  -- queue task ' . $task->getMethod() . PHP_EOL);
            $start = microtime(true);

            if (!$isPriority && $this->issetPriorityQueue()) {break;}

            $this->getObjectManager()->remove($task);
            $this->getObjectManager()->flush();

            $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($task->getAccessToken());

            $data = unserialize($task->getData());

            $res = $vkApi->request($task->getMethod(), $data);

            $res = $res->getResponse();
            if ($res) {
                $clientId = 0;
                if (isset($data['client_id'])) {
                    $clientId = $data['client_id'];
                }
                switch ($task->getMethod()) {
                    case 'ads.getStatistics':
                        $this->getServiceLocator()->get('StatsModule\Logic\StatsLogger')->saveVkData($res);
                        break;
                    case 'ads.getAds':
                        $resultAdsArray = [];
                        $vkAds = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(['vk_client_id' => $clientId]);
                        foreach ($vkAds as $adObject) {
                            $resultAdsArray[$adObject->getId()] = $adObject;
                        }
                        unset($vkAds);
                        if (0&&count($res) === 2000) {
                            $taskData = unserialize($task->getData());
                            $taskData['offset'] = isset($taskData['offset']) ? $taskData['offset'] + 1999 : 1999;
                            $this->pushIntoQueue('ads.getAds', $task->getAccessToken(), serialize($taskData)
                            );
                        }
                        foreach ($res as $ad) {
                            $needUpdate = false;
                            if (isset($resultAdsArray[$ad->id])) {
                                $vkAd = $resultAdsArray[$ad->id];
                                foreach ($ad as $key => $value) {
                                    if ($vkAd->get($key) !== false && $vkAd->get($key) != $value) {
                                        $needUpdate = true;
                                        $vkAd->set($key, $value);
                                    }

                                    if ($ad->status != self::AD_STATUS_DELETE) {
                                        $adRejectReason = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAdsRejectionReasons')->findBy(['id' => $ad->id]);
                                        if ($ad->approved == self::AD_APPROVED_REJECT) {
                                            if (!$adRejectReason) {
                                                $this->pushIntoQueue('ads.getRejectionReason', $task->getAccessToken(),
                                                    serialize(['account_id' => $data['account_id'], 'ad_id' => $ad->id])
                                                );
                                                $this->getObjectManager()->flush();
                                            }

                                        } elseif ($adRejectReason) {
                                            $this->getObjectManager()->remove(reset($adRejectReason));
                                            $this->getObjectManager()->flush();
                                        }
                                    } elseif ($vkAd->getBidderControl()) {
                                        $vkAd->setBidderControl(0);
                                        $needUpdate = true;
                                    }
                                }
                                if ($needUpdate) {
                                    $this->getObjectManager()->persist($vkAd);
                                }
                            }
                            else {
                                $adsEntity = new VkAds();
                                if (isset($data['client_id'])) {
                                    $adsEntity->setVkClientId($data['client_id']);
                                }

                                foreach ($ad as $key => $value) {
                                    $adsEntity->set($key, $value);
                                }

                                $this->getObjectManager()->persist($adsEntity);
                            }
                            unset($ad);
                        }
                        $this->getObjectManager()->flush();
                        break;
                    case 'ads.getCampaigns':
                        foreach ($res as $vkCampaign) {
                            $campaignForSave = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns')->findBy(['id' => $vkCampaign->id]);
                            $campaignForSave = reset($campaignForSave);

                            if ($campaignForSave) {
                                foreach ($vkCampaign as $key => $value) {
                                    $campaignForSave->set($key, $value);
                                }

                                if ($vkCampaign->status == 2) {
                                    $campaignForSave->setBidderControl(0);
                                }
                                $this->getObjectManager()->persist($campaignForSave);
                            } else {
                                $additionalData = $task->getAdditionalInfo();
                                $campaignEntity = new VkCampaigns();
                                $campaignEntity->setCabinetId($data['account_id']);
                                if (isset($data['client_id'])) {
                                    $campaignEntity->setVkClientId($data['client_id']);
                                }
                                if ($additionalData) {
                                    $additionalData = unserialize($additionalData);
                                    $campaignEntity->setVkAccountId($additionalData['vk_account_id']);
                                }

                                foreach ($vkCampaign as $key => $value) {
                                    $campaignEntity->set($key, $value);
                                }

                                $this->getObjectManager()->persist($campaignEntity);
                            }
                            unset($vkCampaign);

                        }
                        $this->getObjectManager()->flush();
                        break;
                    case 'ads.getAdsLayout':
                        $filter = [];
                        $vkAdsTargeting = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(['vk_client_id' => $clientId]);
                        /**@var $ad VkAds*/
                        foreach ($vkAdsTargeting as $ad) {
                            $filter[] = $ad->getId();
                        }
                        unset($vkAdsTargeting);

                        $vkAdsLayout = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAdsLayout')->findBy(['id' => $filter]);
                        foreach ($res as $adLayout) {
                            $skip = false;
                            $needUpdate = false;
                            foreach ($vkAdsLayout as $vkAd) {
                                if ($vkAd->getId() == $adLayout->id) {
                                    foreach ($adLayout as $key => $value) {
                                        if ($vkAd->get($key) !== false && $vkAd->get($key) != $value) {
                                            $needUpdate = true;
                                            $vkAd->set($key, $value);
                                        }
                                    }
                                    if ($needUpdate) {
                                        $this->getObjectManager()->persist($vkAd);
                                    }
                                    $skip = true;
                                    break;
                                }
                            }
                            if (!$skip) {
                                $adsLayoutEntity = new VkAdsLayout();

                                foreach ($adLayout as $key => $value) {
                                    $adsLayoutEntity->set($key, $value);
                                }

                                $this->getObjectManager()->persist($adsLayoutEntity);
                            }
                            unset($adLayout);
                        }
                        $this->getObjectManager()->flush();
                        break;
                    case 'ads.getRejectionReason':
                        $adRejectReason = new VkAdsRejectionReasons();
                        $adRejectReason->setId($data['ad_id']);
                        if (isset($res->comment)) {
                            $adRejectReason->setComment($res->comment);
                        }
                        if (isset($res->rules)) {
                             $adRejectReason->setRules(serialize($res->rules));
                        }
                        $this->getObjectManager()->persist($adRejectReason);
                        $this->getObjectManager()->flush();
                        break;
                    case 'ads.getAdsTargeting':
                        $filter = [];
                        $vkAds = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(['vk_client_id' => $clientId]);
                        /**@var $ad VkAds*/
                        foreach ($vkAds as $ad) {
                            $filter[] = $ad->getId();
                        }
                        unset($vkAds);
                        /**@var $vkAdTargeting VkAdsTargeting*/
                        $vkAdsTargeting = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAdsTargeting')->findBy(['id' => $filter]);

                        $resultTargetingArray = [];
                        foreach ($vkAdsTargeting as $vkAdTargeting) {
                            $resultTargetingArray[$vkAdTargeting->getId()] = $vkAdTargeting;
                        }
                        unset($vkAdsTargeting);

                        foreach ($res as $targetingFromVk) {
                            if (isset($resultTargetingArray[$targetingFromVk->id])) {
                                $vkAdTargeting = $resultTargetingArray[$targetingFromVk->id];
                                if ($vkAdTargeting->getHash() != md5(serialize((array)$targetingFromVk))) {
                                    $vkAdTargeting->setHash(md5(serialize((array)$targetingFromVk)));
                                    $vkAdTargeting->setData(serialize((array)$targetingFromVk));
                                    $this->getObjectManager()->persist($vkAdTargeting);
                                }
                            }
                            else {
                                $newVkTarget = new VkAdsTargeting();
                                $newVkTarget->setId($targetingFromVk->id);
                                $newVkTarget->setHash(md5(serialize((array)$targetingFromVk)));
                                $newVkTarget->setData(serialize((array)$targetingFromVk));
                                $this->getObjectManager()->persist($newVkTarget);
                            }
                        }
                        $this->getObjectManager()->flush();

                        break;
                    case 'ads.getSuggestions':
                        foreach ($res as $vkCountry) {
                            $country = new VkCountries();
                            $country->setId($vkCountry->id);
                            $country->setName($vkCountry->name);
                            $this->getObjectManager()->persist($country);
                        }
                        $this->getObjectManager()->flush();

                        break;
                    case 'ads.updateAds':
                        $adsForUpdate = json_decode($data['data']);
                        foreach ($adsForUpdate as $adForUpdate) {
                            foreach ($res as $adRes) {
                                if (isset($adRes->id) && !isset($adRes->error_code) && $adRes->id == $adForUpdate->ad_id) {
                                    $vkAd = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(['id' => $adRes->id]);
                                    if ($vkAd && $vkAd = reset($vkAd)) {
                                        if (isset($adForUpdate->status)) { // TODO!!!!
                                            $vkAd->setStatus($adForUpdate->status);
                                        }
                                        $this->getObjectManager()->persist($vkAd);
                                        break;
                                    }
                                }
                            }
                        }

                        $this->getObjectManager()->flush();
                        break;
                    case 'ads.deleteAds':
                        $adsForDelete = json_decode($data['ids']);
                        foreach ($adsForDelete as $key => $adForDelete) {
                            if (isset($res[$key]) && $res[$key] == 0) {
                                $vkAd = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(['id' => $adForDelete]);
                                if ($vkAd && $vkAd = reset($vkAd)) {
                                    $vkAd->setStatus(self::AD_STATUS_DELETE);
                                    $this->getObjectManager()->persist($vkAd);
                                }
                            }
                        }

                        $this->getObjectManager()->flush();
                        break;
                    case 'ads.createAds':
                        $adsForDelete = json_decode($data['ids']);
                        foreach ($adsForDelete as $key => $adForDelete) {
                            if (isset($res[$key]) && $res[$key] == 0) {
                                $vkAd = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(['id' => $adForDelete]);
                                if ($vkAd && $vkAd = reset($vkAd)) {
                                    $vkAd->setStatus(self::AD_STATUS_DELETE);
                                    $this->getObjectManager()->persist($vkAd);
                                }
                            }
                        }

                        $this->getObjectManager()->flush();
                        break;
                }
            } else {}
            unset($task);
            unset($res);
            echo('  -- task time '. ($taskTime = microtime(true) - $start) . ' sec' . PHP_EOL);
        }
    }

    public function adsManager($vkAdId, $action, $formData = [], $callback = null)
    {
        /**@var $vkCampaign VkCampaigns*/
        /**@var $vkAd VkAds*/
        /**@var $vkAccount VkAccounts*/
        $result = $error = null;
        $vkAdsIds = !is_array($vkAdId) ? array($vkAdId) : $vkAdId;

        if (!$vkAdsIds ||
            !($action === self::ADS_ACTION_STOP
                || $action === self::ADS_ACTION_START
                || $action === self::ADS_ACTION_DELETE
                || $action === self::ADS_ACTION_COPY
                || $action === self::ADS_ACTION_SETTINGS)
        ) {
            return false;
        }

        foreach ($formData as $field) {
            $result[$field['name']] = $field['value'];
        }
        if ($result) {
            $error = $this->validateFormData($result);
        }

        if ($error) {
            return ['status' => 0, 'error' => $error];
        }
        $data = $arResult = $params = [];

        foreach ($vkAdsIds as $adId) {
            $vkAd = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(['id' => $adId]);
            $vkAd = reset($vkAd);
            if ($vkAd) {
                $vkCampaign = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns')->findBy(['id' => $vkAd->getCampaignId()]);
                $vkCampaign = reset($vkCampaign);
                if ($vkCampaign) {
                    $vkAccount = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts')->findBy(['id' => $vkCampaign->getVkAccountId()]);
                    $vkAccount = reset($vkAccount);
                    if ($vkAccount) {
                        switch ($action) {
                            case self::ADS_ACTION_STOP:
                            case self::ADS_ACTION_START:
                                $data[] = ['account_id' => $vkCampaign->getCabinetId(), 'access_token' => $vkAccount->getAccessKey(), 'data' => ['ad_id' => $adId, 'status' => $action]];
                                break;
                            case self::ADS_ACTION_DELETE:
                                $data[] = ['account_id' => $vkCampaign->getCabinetId(), 'access_token' => $vkAccount->getAccessKey(), 'data' => $adId];
                                break;
                            case self::ADS_ACTION_SETTINGS:
                                $settings = ['ad_id' => $adId];
                                if (isset($result['ad-name']) && strlen($result['ad-name'])) {
                                    $settings['name'] = $result['ad-name'];
                                }
                                if (isset($result['cpm-type']) && isset($result['ad-cpm']) && ((float) $result['ad-cpm'])) {
                                    if ($result['cpm-type'] == 1) {
                                        $settings['cpm'] = $result['ad-cpm'];
                                    }
                                    if ($result['cpm-type'] == 2) {
                                        $settings['cpm'] = $vkAd->getCpm() / 100 + $result['ad-cpm'];
                                    }
                                }
                                if (count($settings) > 1) {
                                    $data[] = ['account_id' => $vkCampaign->getCabinetId(), 'access_token' => $vkAccount->getAccessKey(), 'data' => $settings];
                                }
                                break;
                            default:
                                return false;
                        }
                    }
                }
            }
        }

        foreach ($data as $forSort) {
            $arResult[$forSort['access_token']][$forSort['account_id']][] = $forSort['data'];
        }

        foreach ($arResult as $accessToken => $adData) {
            foreach ($adData as $accountId => $resultData) {
                foreach (array_chunk($resultData, self::ADS_MAX_GROUP_COUNT) as $chunk) {
                    $params[] = [
                        'method' => $this->_methodConfig[$action],
                        'access_token' => $accessToken,
                        'data' =>
                            serialize([
                                'account_id' => $accountId,
                                ($action !== self::ADS_ACTION_DELETE ? 'data' : 'ids') => json_encode($chunk),
                            ])
                    ];
                }

            }
        }

        foreach ($params as $task) {
            $this->pushIntoQueue($task['method'], $task['access_token'], $task['data'], true);
        }

        $this->getObjectManager()->flush();

        return ['status' => 1];
    }

    private function getVkAccounts()
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts')->findAll();
    }

    /**@var $vkAccount VkAccounts*/
    private function getVkCabinets($vkAccount)
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\Cabinets')->findBy(['vk_account_id' => $vkAccount->getId(), 'account_status' => 1]);
    }

    private function getVkClients($vkAccount, $vkCabinet)
    {
        /**@var $vkCabinet Cabinets*/
        /**@var $vkAccount VkAccounts*/
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkClients')->findBy(['bidder_account_id' => $vkAccount->getId(), 'account_id' => $vkCabinet->getAccountId()]);
    }


    private function getVkCampaigns($vkCabinet, $vkAccount, $vkClient = null)
    {
        /**@var $vkCabinet Cabinets*/
        /**@var $vkAccount VkAccounts*/
        /**@var $vkClient VkClients*/
        $filter = ['cabinet_id' => $vkCabinet->getAccountId(), 'vk_account_id' => $vkAccount->getId(), 'status' => [0, 1]];

        if ($vkClient) {
            $filter['vk_client_id'] = $vkClient->getId();
        }

        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns')->findBy($filter);
    }

    private function getVkAds(array $vkCampaignsIds)
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(['campaign_id' => $vkCampaignsIds, 'status' => [0, 1]]);
    }

    private function pushIntoQueue($method, $accessToken, $data, $priorityQueue = false, $additionalInfo = '')
    {
        $issetTask = $priorityQueue ?
            $this->getObjectManager()->getRepository('\Socnet\Entity\VkPriorityQueue')->findBy(['unique_sign' => md5($data.$method)]) :
            $this->getObjectManager()->getRepository('\Socnet\Entity\VkDataUpdateQueue')->findBy(['unique_sign' => md5($data.$method)]);

        if ($issetTask) {return true;}

        $vkDataUpdateQueue = $priorityQueue ? new VkPriorityQueue() : new VkDataUpdateQueue();
        $vkDataUpdateQueue->setMethod($method);
        $vkDataUpdateQueue->setAccessToken($accessToken);
        $vkDataUpdateQueue->setData($data);
        $vkDataUpdateQueue->setUniqueSign(md5($data.$method));
        if ($additionalInfo) {
            $vkDataUpdateQueue->setAdditionalInfo($additionalInfo);
        }

        $this->getObjectManager()->persist($vkDataUpdateQueue);

        return true;

    }

    private function collectCampaignsIds($vkCampaigns)
    {
        /**@var $vkCampaign VkCampaigns*/
        $ids = [];
        foreach ($vkCampaigns as $vkCampaign) {
            $ids[] = $vkCampaign->getId();
        }
        return $ids;
    }

    private function collectAdsIds($vkAds)
    {
        /**@var $vkAd VkAds*/
        $ids = [];
        foreach ($vkAds as $vkAd) {
            $ids[] = $vkAd->getId();
        }
        return $ids;
    }

    private function getVkDataUpdateQueue($accessToken = '')
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkDataUpdateQueue')->findAll();
    }

    private function issetPriorityQueue()
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkAdsProcessing')->findAll()
            || $this->getObjectManager()->getRepository('\Socnet\Entity\VkPriorityQueue')->findAll();
    }

    private function getVkPriorityQueue()
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkPriorityQueue')->findAll();
    }

    private function isTimeForGetQuery($type)
    {
        if (!isset($this->_lockConfig[$type])) {return false;}
        $this->createCronCheckTable();
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $rc = $dbAdapter->query(
            "SELECT time
             FROM cronCheck
             WHERE id = " . $type,
            $dbAdapter::QUERY_MODE_EXECUTE
        );

        $resultSet = new ResultSet;
        $resultSet->initialize($rc);
        $arrayResult = $resultSet->toArray();
        $lock = reset($arrayResult);

        if (!$lock || $lock['time'] + $this->_lockConfig[$type] < time()) {
            $dbAdapter->query(
                "INSERT INTO " . self::CRON_CHECK_TABLE . " (id, time) VALUES (" . $type . "," . time() . ") ON DUPLICATE KEY UPDATE time=". time() .";",
                $dbAdapter::QUERY_MODE_EXECUTE
            );
            return true;
        }
        return false;
    }

    private function createCronCheckTable()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $dbAdapter->query(
            "CREATE TABLE IF NOT EXISTS " . self::CRON_CHECK_TABLE . " (
            `id` int(11) NOT NULL DEFAULT '1',
            `time` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `id` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=cp1251;",
            $dbAdapter::QUERY_MODE_EXECUTE
        );
    }

    public function getAdPreviewContent($adId)
    {
        $adLayout = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAdsLayout')->findBy(['id' => $adId]);
        $content = '';
        if ($adLayout && ($adLayout = reset($adLayout))) {
            /**@var $adLayout VkAdsLayout*/
            /**@var $adTargeting VkAdsTargeting*/

            $adTargeting = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAdsTargeting')->findBy(['id' => $adId]);
            $content = '<div class="ad-picture-container"><a target="_blank" href=' . $adLayout->getLinkUrl() . ' class="bidder_ad_box">';
            $content .= '<div class="bidder_ad_title">' . $adLayout->getTitle() . '</div>';
            $content .= '<div class="bidder_ad_domain">' . $adLayout->getLinkDomain() . '</div>';
            $content .= '<span><img src="' . $adLayout->getImageSrc() . '"></span>';
            if ($adLayout->getDescription()) {
                $content .= '<div class="bidder_ad_desc">' . $adLayout->getDescription() . '</div>';
            }
            $content .= '</a></div>';
            if ($adTargeting && ($adTargeting = unserialize(reset($adTargeting)->getData()))) {
                $content .= '<div class="ad-targeting-container">';

                if (isset($adTargeting['count'])) {
                    $content .= '<b>Аудитория: <big>'. (number_format($adTargeting['count'], 0, '.', '<delimetr>')) . '</big> '. $this->getServiceLocator()->get('Base\String')->format($adTargeting['count'],['человек','человека','человек']) .'</b>';
                }
                $content .= '<ul>';
                if (isset($adTargeting['sex'])) {
                    $content .= '<li>Пол '. ($adTargeting['sex'] == 1 ? 'женский' : 'мужской') . '</li>';
                }
                if (isset($adTargeting['age_from']) || isset($adTargeting['age_to'])) {
                    $content .= '<li>Возраст ';
                    if (isset($adTargeting['age_from'])) {
                        $content .= $adTargeting['age_from'] . ' ';
                    }
                    if (isset($adTargeting['age_to'])) {
                        $content .= 'до ' . $adTargeting['age_to'];
                    }
                    $content .= '</li>';
                }
                if (isset($adTargeting['country'])) {
                    $adCountry = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCountries')->findBy(['id' => $adTargeting['country']]);

                    $content .= '<li>Страна '. reset($adCountry)->getName() . '</li>';
                }
                if (isset($adTargeting['retargeting_groups'])) {
                    $content .= '<li>Используются группы ретаргетинга</li>';
                }
                $content .= '</ul></div>';
            }
        }
        return $content;
    }

    protected function getObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->_objectManager;
    }

    private function convert($size)
    {
        $unit = array('b','kb','mb','gb','tb','pb');
        return @round($size / pow(1024, ($i = floor(log($size,1024)))), 2).' '.$unit[$i];
    }

    private function validateFormData($formData)
    {
        $message = '';
        if (isset($formData['ad-name'])
            && !$this->validateTitle($formData['ad-name'])
        ) {
            $message .= 'Ошибка в указании названия объявления.' . PHP_EOL;
        }
        if (isset($formData['cpm-type'])
            && !($formData['cpm-type'] == 1 || $formData['cpm-type'] == 2)
        ) {
            $message .= 'Указан не правильный тип изменения цены.' . PHP_EOL;
        }
        if (isset($formData['ad-cpm']) && $formData['ad-cpm']
            && !((float) $formData['ad-cpm'])
        ) {
            $message .= 'Ошибка в указании цены.' . PHP_EOL;
        }
        return $message;
    }

    private function validateTitle($title)
    {
        return !(mb_strlen($title, 'UTF-8') > 59 || preg_match('/[^(\w)|(\x7F-\xFF)|(\s)|(!@#$%^&*()_+=:<>)]/', $title));
    }

}
