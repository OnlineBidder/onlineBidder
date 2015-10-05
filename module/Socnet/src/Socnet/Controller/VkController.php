<?php

namespace Socnet\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Socnet\Entity\VkAdsProcessing;
use Socnet\Entity\VkAds;
use Zend\View\Model\ViewModel;
use Socnet\Entity\VkCampaigns;
use Socnet\Entity\Cabinets;
use Socnet\Entity\VkAccounts;
use Socnet\Entity\VkClients;
use Socnet\Entity\TestCpm;
use Socnet\Controller\SocnetController;
use getjump\Vk;
use Doctrine\ORM\EntityRepository;
use \Socnet\Logic\VkLogic;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;


class VkController extends AbstractActionController
{
    /*
     постоянно проигрывать объявление и стопать его
     как только цтр падает ниже значения и возобновлять
     его например через 30 минут
    */
    const ALGORITHM1 = 1;

    /*
     запускать по чуть-чуть показов (5000) каждый период (10 минут)
     и в случае повышения цтр по докаточной статистике увеличивать
     показы вдвое.
    (то есть если 5000 будет успешними, стартует 10000.
     Если и 10000 будут успешными, то 20000.
     Если 20000 не будут успешными, возвращается до 5000 снова)
    */
    const ALGORITHM2 = 2;

    /*
     запускаем объяву с максимальным cpm, постепенно снижаем по 5%, смотрим стату, сравниваем cpc
    */
    const ALGORITHM3 = 3;


    const ALGORITHM4 = 4;


    const CPM_STEP_UP = 1.05; //5%
    const CPM_STEP_DOWN = 0.95; //5%
    const CPM_MAX = 8;
    const CPM_MIN = 0.3;

    const CPM_BID_UP = 0.7;

    const ADS_GROUP_COUNT = 5; //�� ������� ����� � ������� �������� � ��

    const TEST_STEP_PERCENT = 0.9; //10%

    protected $_objectManager;
    /**
     * @var EntityRepository
     */
    protected $campaignsRepository;
    /**
     * @var EntityRepository
     */
    protected $cabinetsRepository;
    /**
     * @var EntityRepository
     */
    protected $vkAccountsRepository;
    /**
     * @var EntityRepository
     */
    protected $vkAdsRepository;
    /**
     * @var EntityRepository
     */
    protected $vkAdsProcessingRepository;

    private $vkAdsProc = [];
    private $arActiveAds = [];
    private $arAdsTokens = [];

    private $userId = null;

    private $algorithmConfig = [
        self::ALGORITHM1 => 'algorithm1',
        self::ALGORITHM2 => 'algorithm2',
        self::ALGORITHM3 => 'algorithm3',
        self::ALGORITHM4 => 'algorithm4',
    ];

    public function onDispatch( \Zend\Mvc\MvcEvent $e )
    {
        // date_default_timezone_set('Europe/Kaliningrad');
        $this->campaignsRepository = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns');
        $this->cabinetsRepository = $this->getObjectManager()->getRepository('\Socnet\Entity\Cabinets');
        $this->vkAccountsRepository = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts');
        $this->vkAdsRepository = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds');
        $this->vkAdsProcessingRepository = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAdsProcessing');
        $sm = $this->getServiceLocator();
        $auth = $sm->get('zfcuserauthservice');
        if (!$this->userId && $auth->hasIdentity()) {
            $this->userId = $auth->getIdentity()->getId();
        }
        return parent::onDispatch( $e );
    }

    public function adsManagerAction()
    {
        set_time_limit(0);
        echo( 'getAdsStatistic'."<br>");
        $this->getServiceLocator()->get('Socnet\Logic\VkLogic')->getAdsStatistic();
        echo( 'getAdsInformation'."<br>");
        //$this->getServiceLocator()->get('Socnet\Logic\VkLogic')->getAdsInformation();
        echo( 'collateQueue'."<br>");
        $this->getServiceLocator()->get('Socnet\Logic\VkLogic')->collateQueue();

        echo( 'done!'."\r\n");

        die;
        $countries = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCountries')->findAll();
        $vkApi = Vk\Core::getInstance();

        foreach ($countries as $country) {

            $res = $vkApi->request('database.getCities', ['country_id' => $country->getId(), 'need_all' => 1, 'count' => 1000])->getResponse();
            foreach ($res as $vkCity) {
                //var_dump($vkCity);
                $city = new \Socnet\Entity\VkCities();
                $city->setId($vkCity->cid);
                $city->setName($vkCity->title);
                $city->setCountryId($country->getId());
                $this->getObjectManager()->persist($city);
            }
            $this->getObjectManager()->flush();
sleep(2);
        }


   /*     $countries = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCountries')->findAll();
        $vkApi = Vk\Core::getInstance();

        foreach ($countries as $country) {

            $res = $vkApi->request('database.getCities', ['country_id' => $country->getId(), 'need_all' => 1, 'count' => 1000])->getResponse();
            foreach ($res as $vkRegion) {
                // var_dump($vkRegion);
                $region = new \Socnet\Entity\VkRegions();
                $region->setId($vkRegion->region_id);
                $region->setName($vkRegion->title);
                $this->getObjectManager()->persist($region);
            }
            $this->getObjectManager()->flush();
            sleep(2);
        }*/


        return true;
    }

    public function adsManagerDaemonAction()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
     //   sleep(5);
     //   return false;

        $fp = fopen('/var/log/11111.txt', 'a');
        fwrite($fp, var_export(PHP_EOL, true));
        fwrite($fp, var_export('---------------------------------', true));
        fwrite($fp, var_export(PHP_EOL, true));
        fwrite($fp, var_export('Start daemon ', true));
        fwrite($fp, var_export(date("Y-m-d H:i:s"), true));
        echo( 'collatePriorityQueue'.PHP_EOL);
        $this->getServiceLocator()->get('Socnet\Logic\VkLogic')->collateQueue(true);
        echo('adsProcessing'.PHP_EOL);
        $this->adsProcessingAction();
        echo( 'initActiveAds'.PHP_EOL);
        $this->initActiveAds();
        echo( 'processingActiveAds'.PHP_EOL);
        $this->processingActiveAds();
        echo( 'adsProcessing'.PHP_EOL);
        $this->adsProcessingAction();
       // echo( 'getAdsStatistic'.PHP_EOL);
       // $this->getServiceLocator()->get('Socnet\Logic\VkLogic')->getAdsStatistic();
        echo( 'getAdsInformation'.PHP_EOL);
        $this->getServiceLocator()->get('Socnet\Logic\VkLogic')->getAdsInformation();
        echo( 'collateQueue'.PHP_EOL);
        $this->getServiceLocator()->get('Socnet\Logic\VkLogic')->collateQueue();
        gc_collect_cycles();
        fwrite($fp, var_export(PHP_EOL, true));
        fwrite($fp, var_export('Stop daemon ', true));
        fwrite($fp, var_export(date("Y-m-d H:i:s"), true));

        return false;
    }

    public function testAction()
    {
        echo "<pre>";
        var_dump($this->getServiceLocator()->get('Socnet\VkBase')->getCabinetsByUser(9));
        die;
        for ($i = 1; $i < 100; ++$i) {
            $vkAdsProcessing = new VkAdsProcessing();
            $vkAdsProcessing->setAdId($i+345);
            $vkAdsProcessing->setAccountId($i);
            $vkAdsProcessing->setStatus(1);
            $vkAdsProcessing->setAccessToken($i*rand(5,67));
            $this->getObjectManager()->persist($vkAdsProcessing);

        }
        $this->getObjectManager()->flush();
return 1;
    }

    private function initActiveAds()
    {
        /**@var $campaign VkCampaigns*/
        /**@var $vkAd VkAds*/

        $campaigns = $this->campaignsRepository->findBy(['bidder_control' => 1, 'status' => 1]);

        foreach ($campaigns as $campaign) {
            $cabinetFilter = ['account_id' => $campaign->getCabinetId()];
            if ($campaign->getVkAccountId() > 4) {
                $cabinetFilter['vk_account_id'] = $campaign->getVkAccountId();
            }
            $cabinet = $this->cabinetsRepository->findBy($cabinetFilter);
            $cabinet = reset($cabinet);
            $vkUserId = $cabinet->getVkUserId();
            $vkAccount = $this->vkAccountsRepository->findBy(['vk_user_id' => $vkUserId]);
            $vkAccount = reset($vkAccount);
            $vkAds = $this->vkAdsRepository->findBy(['campaign_id' => $campaign->getId(), 'bidder_control' => 1]);

            foreach ($vkAds as $vkAd) {
                $this->vkAdsProc[$vkAd->getId()] = $this->vkAdsProcessingRepository->findBy(['ad_id' => $vkAd->getId()]);

                if ($vkAd->getStatus() == VkLogic::AD_STATUS_PLAY && $vkAd->getAlgorithm() != self::ALGORITHM3) {
                    $this->arActiveAds[$vkAccount->getAccessKey()][$cabinet->getAccountId()][$vkAd->getId()] = $vkAd->getImpressions() ? ($vkAd->getClicks() / $vkAd->getImpressions()) : 0;
                    $this->arAdsTokens[$vkAd->getId()] = $vkAccount->getAccessKey();
                } elseif (($this->isTimeForAdsPlay($campaign)) && !$this->vkAdsProc[$vkAd->getId()] && $vkAd->getStatus() == VkLogic::AD_STATUS_STOP && (($vkAd->getBidderUpdateTime() + (int) $vkAd->getOffMinutes() * 60) <= time())
                ) {
                    $vkAdsProcessing = new VkAdsProcessing();
                    $vkAdsProcessing->setAdId($vkAd->getId());
                    $vkAdsProcessing->setAccountId($cabinet->getAccountId());
                    $vkAdsProcessing->setStatus(VkLogic::AD_STATUS_PLAY);
                    $vkAdsProcessing->setAccessToken($vkAccount->getAccessKey());
                    $vkAd->setBidderUpdateTime(time());
                    if ($vkAd->getAlgorithm() == self::ALGORITHM3) {
                        $this->algorithm3($vkAd, $vkAdsProcessing);
                    }
                    $this->getObjectManager()->persist($vkAdsProcessing);
                }
            }
        }
        $this->getObjectManager()->flush();

    }

    private function isTimeForBidUp()
    {
        return  ((date('H') == 9 || date('H') == 12 || date('H') == 18) && (date('i') > 4 && date('i') < 10));
    }

    private function isTimeForAdsPlay(VkCampaigns $campaign)
    {
        $stopFrom = $campaign->getStopFrom();
        $stopTo = $campaign->getStopTo();

        return $stopFrom > date('H') || $stopTo <= date('H') || $stopFrom === $stopTo || ($stopTo === 24 && $stopFrom !== 0 && date('H') === '00');
    }

    private function algorithm1($stat, $vkAd, $account_id)
    {
        /**@var $vkAd VkAds*/

        $prevImpressions = $vkAd->getImpressions();
        $prevClicks = $vkAd->getClicks();
        $statImpressions = isset($stat->impressions) ? $stat->impressions : 0;
        $statClicks = isset($stat->clicks) ? $stat->clicks : 0;
        $statSpent = isset($stat->spent) ? $stat->spent : 0;

        $checkImpressions = $statImpressions - $prevImpressions;
        $checkClicks = $statClicks - $prevClicks;
        if ($checkImpressions < 0) {
            $checkImpressions = $statImpressions;
            $checkClicks = $statClicks;
            $vkAd->setImpressions(0);
            $vkAd->setClicks(0);
        }
        if ($statImpressions - $vkAd->getImpressions() >= $vkAd->getMinCounter()) {
            $vkAd->setImpressions($statImpressions);
            $vkAd->setClicks($statClicks);
            $vkAd->setSpent($statSpent);

            if (!$this->vkAdsProc[$vkAd->getId()] && $checkImpressions &&
                $checkClicks &&
                (($checkClicks / $checkImpressions) < ($vkAd->getMinCtr() / 100))
            ) {
                $vkAdsProcessingRepository = new VkAdsProcessing();
                $vkAdsProcessingRepository->setAdId($vkAd->getId());
                $vkAdsProcessingRepository->setAccountId($account_id);
                $vkAdsProcessingRepository->setStatus(0);
                $vkAdsProcessingRepository->setAccessToken($this->arAdsTokens[$vkAd->getId()]);
                $vkAd->setBidderUpdateTime(time());
                $this->getObjectManager()->persist($vkAdsProcessingRepository);
            }
        }
    }

    private function algorithm2($stat, $vkAd, $account_id)
    {
        /**@var $vkAd VkAds*/

        $prevImpressions = $vkAd->getImpressions();
        $prevClicks = $vkAd->getClicks();
        $prevSpent = $vkAd->getSpent();
        $statImpressions = isset($stat->impressions) ? $stat->impressions : 0;
        $statClicks = isset($stat->clicks) ? $stat->clicks : 0;
        $statSpent = isset($stat->spent) ? $stat->spent : 0;
        $prevSpent = $statSpent >= $prevSpent ? $prevSpent : $statSpent;

        $checkImpressions = $statImpressions - $prevImpressions;
        $checkClicks = $statClicks - $prevClicks;
        if ($checkImpressions < 0) {
            $checkImpressions = $statImpressions;
            $checkClicks = $statClicks;
            $vkAd->setImpressions(0);
            $vkAd->setClicks(0);
        }
        if (($statImpressions - $vkAd->getImpressions() >= $vkAd->getMinCounter())
            || $this->isTimeForBidUp()
        ) {
            $vkAd->setImpressions($statImpressions);
            $vkAd->setClicks($statClicks);
            $vkAd->setSpent($statSpent);

            if (!$this->vkAdsProc[$vkAd->getId()]) {

                $cpc = $statClicks ? $stat->spent / $statClicks : $stat->spent;

             //   $cpc = $prevClicks ? $prevSpent / $prevClicks : $prevSpent;

                $vkAdsProcessingRepository = new VkAdsProcessing();
                $vkAdsProcessingRepository->setAdId($vkAd->getId());
                $vkAdsProcessingRepository->setAccountId($account_id);

                if ($checkImpressions &&
                    (($checkClicks / $checkImpressions) < ($vkAd->getMinCtr() / 100))) {
                    $vkAdsProcessingRepository->setStatus(0);
                }

                $vkAdsProcessingRepository->setAccessToken($this->arAdsTokens[$vkAd->getId()]);
                $vkAd->setBidderUpdateTime(time());
                if ($vkAd->getDesireCpc()) {
                    if ($cpc > $vkAd->getDesireCpc()) {
                        $newCpm = round(($vkAd->getCpm() / 100) * self::CPM_STEP_DOWN, 2);
                        $newCpm = $newCpm < self::CPM_MIN ? self::CPM_MIN : $newCpm;
                        if ($newCpm < self::CPM_BID_UP * self::CPM_STEP_DOWN && $this->isTimeForBidUp()) {
                            $newCpm = self::CPM_BID_UP;
                        }

                        $vkAdsProcessingRepository->setCpm($newCpm);
                        $vkAdsProcessingRepository->setCtr(($newCpm / $vkAd->getDesireCpc() / 10) * 0.8);
                    } else {
                        $newCpm = round(($vkAd->getCpm() / 100) * self::CPM_STEP_UP, 2);
                        $newCpm = $newCpm > self::CPM_MAX ? self::CPM_MAX: $newCpm;
                        if ($newCpm < self::CPM_BID_UP * self::CPM_STEP_DOWN && $this->isTimeForBidUp()) {
                            $newCpm = self::CPM_BID_UP;
                        }

                        $vkAdsProcessingRepository->setCpm($newCpm);
                        $vkAdsProcessingRepository->setCtr(($newCpm / $vkAd->getDesireCpc() / 10) * 0.8);
                    }
                }
                $this->getObjectManager()->persist($vkAdsProcessingRepository);
            }
        }

    }

    private function algorithm3(VkAds $vkAd, VkAdsProcessing $vkAdsProcessing)
    {
        $currentLimit = $vkAd->getAllLimit();
        $currentCpm = $vkAd->getCpm() / 100;
        $newCpm = round($currentCpm * self::TEST_STEP_PERCENT, 2);
        $minCounter = $vkAd->getMinCounter();
        $newLimit = $currentLimit + round($newCpm * ($minCounter / 1000));
        $vkAdsProcessing->setAllLimit($newLimit);
        $vkAdsProcessing->setCpm($newCpm);
    }

    private function algorithm4($stat, $vkAd, $account_id)
    {
        /**@var $vkAd VkAds*/

        $prevImpressions = $vkAd->getImpressions();
        $prevClicks = $vkAd->getClicks();
        $prevSpent = $vkAd->getSpent();
        $statImpressions = isset($stat->impressions) ? $stat->impressions : 0;
        $statClicks = isset($stat->clicks) ? $stat->clicks : 0;
        $statSpent = isset($stat->spent) ? $stat->spent : 0;
        $prevSpent = $statSpent >= $prevSpent ? $prevSpent : $statSpent;

        $checkImpressions = $statImpressions - $prevImpressions;
        $checkClicks = $statClicks - $prevClicks;
        if ($checkImpressions < 0) {
            $checkImpressions = $statImpressions;
            $checkClicks = $statClicks;
            $vkAd->setImpressions(0);
            $vkAd->setClicks(0);
        }
        if (($statImpressions - $vkAd->getImpressions() >= $vkAd->getMinCounter())
            || $this->isTimeForBidUp()
        ) {
            $vkAd->setImpressions($statImpressions);
            $vkAd->setClicks($statClicks);
            $vkAd->setSpent($statSpent);

            if (!$this->vkAdsProc[$vkAd->getId()]) {

              //  $cpc = $statClicks ? $stat->spent / $statClicks : $stat->spent;

                $cpc = $prevClicks ? $statSpent - $prevSpent / $statClicks - $prevClicks : $prevSpent;

                $vkAdsProcessingRepository = new VkAdsProcessing();
                $vkAdsProcessingRepository->setAdId($vkAd->getId());
                $vkAdsProcessingRepository->setAccountId($account_id);

                if ($checkImpressions &&
                    (($checkClicks / $checkImpressions) < ($vkAd->getMinCtr() / 100))) {
                    $vkAdsProcessingRepository->setStatus(0);
                }

                $vkAdsProcessingRepository->setAccessToken($this->arAdsTokens[$vkAd->getId()]);
                $vkAd->setBidderUpdateTime(time());
                if ($vkAd->getDesireCpc()) {
                    if ($cpc > $vkAd->getDesireCpc()) {
                        $newCpm = ($vkAd->getCpm() / 100) - self::CPM_STEP;
                        $newCpm = $newCpm < self::CPM_MIN ? self::CPM_MIN : $newCpm;
                        if ($newCpm < self::CPM_BID_UP - self::CPM_STEP && $this->isTimeForBidUp()) {
                            $newCpm = self::CPM_BID_UP;
                        }

                        $vkAdsProcessingRepository->setCpm($newCpm);
                        $vkAdsProcessingRepository->setCtr(($newCpm / $vkAd->getDesireCpc() / 10) * 0.8);
                    } else {
                        $newCpm = ($vkAd->getCpm() / 100) + self::CPM_STEP;
                        $newCpm = $newCpm > self::CPM_MAX ? self::CPM_MAX : $newCpm;
                        if ($newCpm < self::CPM_BID_UP - self::CPM_STEP && $this->isTimeForBidUp()) {
                            $newCpm = self::CPM_BID_UP;
                        }

                        $vkAdsProcessingRepository->setCpm($newCpm);
                        $vkAdsProcessingRepository->setCtr(($newCpm / $vkAd->getDesireCpc() / 10) * 0.8);
                    }
                }
                $this->getObjectManager()->persist($vkAdsProcessingRepository);
            }
        }

    }

    private function processingActiveAds()
    {
        /**@var $vkAd VkAds*/

        $adsStatistic = $this->getAdsStatistic($this->arActiveAds);

        if ($adsStatistic && is_array($adsStatistic)) {

            foreach ($adsStatistic as $account_id => $adStatistic) {

                foreach ($adStatistic as $eachAdStatistic) {
                    foreach ($eachAdStatistic as $adStat) {
                        if (isset($adStat->stats) && $adStat->stats) {
                            $stat = reset($adStat->stats);
                            $vkAd = $this->vkAdsRepository->findBy(['id' => $adStat->id]);
                            $vkAd = reset($vkAd);
                            $algorithm = $vkAd->getAlgorithm();

                            if (isset($this->algorithmConfig[$algorithm])) {
                                $algorithmFunction = $this->algorithmConfig[$algorithm];
                            } else {
                                $algorithmFunction = reset($this->algorithmConfig);
                            }

                            $this->$algorithmFunction($stat, $vkAd, $account_id);
                        }
                    }
                }
            }
        }

        $this->getObjectManager()->flush();
    }

    public function adsProcessingAction()
    {
        $currentTime = time();
        $curDayStartTime = mktime(0, 0, 0, date('n', $currentTime), date('j', $currentTime), date('Y', $currentTime));  // Начало текущих суток

        /**@var $vkAdProcessing VkAdsProcessing*/
        /**@var $vkAdRepository VkAds*/
        /**@var $vkAccount VkAccounts*/

        $vkAccounts = $this->vkAccountsRepository->findAll();

        foreach ($vkAccounts as $vkAccount) {
            $vkAdsProcessingRepository = $this->vkAdsProcessingRepository->findBy(['access_token' => $vkAccount->getAccessKey()]);
            $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($vkAccount->getAccessKey());

            $vkAdsProcessingRepository = array_chunk($vkAdsProcessingRepository, self::ADS_GROUP_COUNT);

            foreach ($vkAdsProcessingRepository as $vkAdsProcessing) {
                $arParamSpec = [];
                $arAdsData = [];
                foreach ($vkAdsProcessing as $key => $vkAdProcessing) {
                    $arAdsData[$vkAdProcessing->getAdId()] = $vkAdProcessing;
                    $arParamSpec[$key]['ad_id'] = $vkAdProcessing->getAdId();
                    if ($vkAdProcessing->getStatus() !== null) {
                        $arParamSpec[$key]['status'] = $vkAdProcessing->getStatus();
                    }
                    if ($vkAdProcessing->getCpm()) {
                        $arParamSpec[$key]['cpm'] = $vkAdProcessing->getCpm();
                    }
                    if ($vkAdProcessing->getAllLimit()) {
                        $arParamSpec[$key]['all_limit'] = $vkAdProcessing->getAllLimit();
                    }
                }

                $arParams = ['account_id' => $vkAdProcessing->getAccountId(), 'data' => json_encode($arParamSpec)];

                $res = $vkApi->request('ads.updateAds', $arParams)->getResponse();

                $vkAdsRepository = $this->vkAdsRepository->findBy(['id' => array_keys($arAdsData)]);

                $arAdsForStatistic = [];
                foreach ($arAdsData as $arAdData) {
                    if ($arAdData->getStatus() == 1) {
                        $arAdsForStatistic[$arAdData->getAdId()] = 1;
                    }
                }

                if ($res) {
                    foreach ($res as $eachRes) {
                        if (!isset($eachRes->error_code) || $eachRes->error_code == 0) {
                            foreach ($vkAdsRepository as $vkAdRepository) {
                                if ($eachRes->id == $vkAdRepository->getId()) {
                                    $vkAdRepository->setStatus($arAdsData[$vkAdRepository->getId()]->getStatus());
                                    if ($arAdsData[$vkAdRepository->getId()]->getCtr()) {
                                        $vkAdRepository->setMinCtr($arAdsData[$vkAdRepository->getId()]->getCtr());
                                    }
                                    if ($arAdsData[$vkAdRepository->getId()]->getAllLimit()) {
                                        $vkAdRepository->setAllLimit($arAdsData[$vkAdRepository->getId()]->getAllLimit());
                                    }
                                    if ($arAdsData[$vkAdRepository->getId()]->getCpm()) {
                                        $vkAdRepository->setCpm($arAdsData[$vkAdRepository->getId()]->getCpm() * 100);
                                    }
                                    if ($arAdsData[$vkAdRepository->getId()]->getStatus() == 1) {
                                        $vkAdStatistic =
                                            $this->getServiceLocator()
                                                ->get('StatsModule\Logic\StatsLogger')
                                                ->getVkSummaryData([$arAdsData[$vkAdRepository->getId()]->getAdId()], $curDayStartTime, $curDayStartTime + 86400)
                                                ->toArray();

                                        if ($vkAdStatistic &&
                                            ($vkAdStatistic = reset($vkAdStatistic))
                                        ) {
                                            $statImpressions = isset($vkAdStatistic['impressions']) ? $vkAdStatistic['impressions'] : 0;
                                            $statClicks = isset($vkAdStatistic['clicks']) ? $vkAdStatistic['clicks'] : 0;
                                            $statSpent = isset($vkAdStatistic['spent']) ? $vkAdStatistic['spent'] : 0;

                                            if ($vkAdRepository->getAlgorithm() == 3) {
                                                $testCpm = new TestCpm();
                                                $testCpm->setAdId($vkAdRepository->getId());
                                                $testCpm->setInsertTime(time());
                                                $testCpm->setCpm($arAdsData[$vkAdRepository->getId()]->getCpm() / self::TEST_STEP_PERCENT);
                                                $testCpm->setCpc(($statSpent - $vkAdRepository->getSpent()) / ($statClicks - $vkAdRepository->getClicks()));
                                                $this->getObjectManager()->persist($testCpm);

                                            }

                                            $vkAdRepository->setImpressions($statImpressions);
                                            $vkAdRepository->setClicks($statClicks);
                                            $vkAdRepository->setImpressionsStep($statImpressions);
                                            $vkAdRepository->setClicksStep($statClicks);
                                            $vkAdRepository->setSpent($statSpent);
                                        }
                                    }
                                }
                            }

                        } else {
                            unset($arAdsForStatistic[$eachRes->id]);
                            if ($eachRes->error_code == 602) {
                                foreach ($vkAdsRepository as $vkAdRepository) {
                                    if ($eachRes->id == $vkAdRepository->getId()) {
                                        $vkAdRepository->setBidderUpdateTime(time()+1000);
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($vkAdsProcessing as $vkAdProcessing) {
                    $this->getObjectManager()->remove($vkAdProcessing);
                }
                $this->getObjectManager()->flush();
            }
        }
        return true;
    }

    public function adsControlAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return false;
        }
        $statuses = [
            'play' => VkLogic::ADS_ACTION_START,
            'stop' => VkLogic::ADS_ACTION_STOP,
            'delete' => VkLogic::ADS_ACTION_DELETE,
            'copy' => VkLogic::ADS_ACTION_COPY,
            'settings' => VkLogic::ADS_ACTION_SETTINGS,
        ];
        $action = $request->getPost('action');
        $data = $request->getPost('form_data');
        $data = $data ? $data : [];

        if (!isset($statuses[$action])) {
            return false;
        }
        $adIds = $request->getPost('adIds');

        $res = $this->getServiceLocator()->get('Socnet\Logic\VkLogic')->adsManager($adIds, $statuses[$action], $data);

        return new JsonModel((array)$res);
    }

    public function doItAction()
    {
        /**@var $campaign VkCampaigns*/
        /**@var $vkAd VkAds*/
        /**@var $vkAccount VkAccounts*/
        /**@var $cabinet Cabinets*/

        $vkAccountId = (int) $this->params()->fromRoute('vk_account_id', 0);
        $cabinetId = (int) $this->params()->fromRoute('cabinet_id', 0);
        $vkAdsIds = $this->getRequest()->getPost('adIds');
        $action = $this->getRequest()->getPost('action');

        if (!$vkAccountId || !$cabinetId || !$vkAdsIds || !$action) {
            echo 1;
            exit;
        }
        $action = $action == 'start' ? 1 : 0;
        $vkAccount = $this->vkAccountsRepository->findBy(['id' => $vkAccountId]);
        $vkAccount = reset($vkAccount);
        $adsInProcessing = $this->vkAdsProcessingRepository->findAll();
        foreach ($adsInProcessing as $adInProcess) {
            $del = array_search($adInProcess->getAdId(), $vkAdsIds);
            if ($del !== false) {
                unset($vkAdsIds[$del]);
            }
        }
        foreach ($vkAdsIds as $vkAdId) {
            $vkAdsProcessingRepository = new VkAdsProcessing();
            $vkAdsProcessingRepository->setAdId($vkAdId);
            $vkAdsProcessingRepository->setAccountId($cabinetId);
            $vkAdsProcessingRepository->setStatus($action);
            $vkAdsProcessingRepository->setAccessToken($vkAccount->getAccessKey());
            $this->getObjectManager()->persist($vkAdsProcessingRepository);
        }
        $this->getObjectManager()->flush();

        echo 1;
        exit;
    }

    public function adsCheckerForceAction()
    {
        ini_set('max_execution_time', 200);

        /**@var $campaign VkCampaigns*/
        /**@var $vkAd VkAds*/
        /**@var $vkAccount VkAccounts*/
        /**@var $cabinet Cabinets*/

        $vkAccountId = (int) $this->params()->fromRoute('vk_account_id', 0);
        $cabinetId = (int) $this->params()->fromRoute('cabinet_id', 0);
        $vkClientId = (int) $this->params()->fromRoute('client_id', 0);
        $vkCampaignId = (int) $this->params()->fromRoute('campaign_id', 0);

        if (!$cabinetId) return true;

        $cabinet = $this->cabinetsRepository->findBy(['vk_account_id' => $vkAccountId, 'account_id' => $cabinetId]);
        $cabinet = reset($cabinet);
        $vkUserId = $cabinet->getVkUserId();
        $vkAccount = $this->vkAccountsRepository->findBy(['vk_user_id' => $vkUserId]);
        $vkAccount = reset($vkAccount);
        $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($vkAccount->getAccessKey());

        $params = ['account_id' => $cabinet->getAccountId(), 'include_deleted' => 1];
        if ($vkClientId) $params['client_id'] = $vkClientId;
        if ($vkCampaignId) {
            $params['campaign_ids'] = json_encode((array) $vkCampaignId);
        }
        $vkCampaigns = $vkApi->request('ads.getCampaigns', $params)->getResponse();

        foreach ($vkCampaigns as $vkCampaign) {
            $campaignForSave = $this->campaignsRepository->findBy(['id' => $vkCampaign->id]);
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
                $campaignEntity = new VkCampaigns();
                $campaignEntity->setCabinetId($cabinetId);
                $campaignEntity->setVkClientId($vkClientId);
                $campaignEntity->setVkAccountId($vkAccountId);
                foreach ($vkCampaign as $key => $value) {
                    $campaignEntity->set($key, $value);
                }

                $this->getObjectManager()->persist($campaignEntity);
            }

            $params = ['account_id' => $cabinet->getAccountId(), 'campaign_ids' => json_encode((array) $vkCampaign->id), 'include_deleted' => 0];
            if ($vkClientId) $params['client_id'] = $vkClientId;
            $adInVk = $vkApi->request('ads.getAds', $params)->getResponse();

            $vkAds = $this->vkAdsRepository->findAll();
            foreach ($adInVk as $ad) {
                $skip = false;
                foreach ($vkAds as $vkAd) {
                    if ($vkAd->getId() == $ad->id) {
                        foreach ($ad as $key => $value) {
                            $vkAd->set($key, $value);
                        }
                        $this->getObjectManager()->persist($vkAd);
                        $skip = true;
                        break;
                    }

                }
                if (!$skip) {
                    $adsEntity = new VkAds();
                    $adsEntity->setVkClientId($vkClientId);

                    foreach ($ad as $key => $value) {
                        $adsEntity->set($key, $value);
                    }

                    $this->getObjectManager()->persist($adsEntity);
                }
            }
            $this->getObjectManager()->flush();
        }
        echo '1';
        exit;
        return true;
    }

    public function adsCheckerAction()
    {
        ini_set('ignore_user_abort', 1);
        ini_set('max_execution_time', 120);

        /**@var $campaign VkCampaigns*/
        /**@var $vkAd VkAds*/
        /**@var $vkAccount VkAccounts*/
        /**@var $cabinet Cabinets*/
        if (date('i') % 29 === 0 || (date('i') % 30 === 0)) {
            $vkAccounts = $this->vkAccountsRepository->findAll();
            foreach ($vkAccounts as $vkAccount) {
                $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($vkAccount->getAccessKey());
                $cabinetsInVk = $vkApi->request('ads.getAccounts')->getResponse();
                foreach ($cabinetsInVk as $cabinetInVk) {

                    $vkCabinetCheck = $this->getObjectManager()->getRepository('\Socnet\Entity\Cabinets')->findBy(['account_id' => $cabinetInVk->account_id, 'vk_account_id' => $vkAccount->getId()]);
                    if (!$vkCabinetCheck) {
                        $vkCabinetEntity = new Cabinets();
                        foreach ($cabinetInVk as $key => $value) {
                            $vkCabinetEntity->set($key, $value);
                        }
                        $vkCabinetEntity->setVkUserId($vkAccount->getVkUserId());
                        $vkCabinetEntity->setVkAccountId($vkAccount->getId());
                        $this->getObjectManager()->persist($vkCabinetEntity);

                        $params = ['account_id' => $cabinetInVk->account_id];
                        $clientsInVk = $vkApi->request('ads.getClients', $params)->getResponse();
                        foreach ($clientsInVk as $client) {
                            $vkClientCheck = $this->getObjectManager()->getRepository('\Socnet\Entity\VkClients')->findBy(['id' => $client->id]);
                            if (!$vkClientCheck) {
                                $vkClientsEntity = new VkClients();
                                $vkClientsEntity->setAccountId($cabinetInVk->account_id);
                                $vkClientsEntity->setBidderAccountId($vkAccount->getId());
                                foreach ($client as $key => $value) {
                                    $vkClientsEntity->set($key, $value);
                                }

                                $this->getObjectManager()->persist($vkClientsEntity);
                            }
                        }
                    }
                }
            }
            $this->getObjectManager()->flush();
        }

        return true;
    }

    public function getRejectReasonAction()
    {
        $vkAdId = (int) $this->params()->fromRoute('vk_account_id', 0);
        if (!$vkAdId) {
            return new JsonModel([
                "data" => [],
            ]);
        }

        $rejectReason = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAdsRejectionReasons')->findBy(['id' => $vkAdId]);
        if (!$rejectReason) {
            return new JsonModel([
                "data" => [],
            ]);
        }

        $rejectReason = reset($rejectReason);

        return new JsonModel([
            "data" => ['comment' => $rejectReason->getComment(), 'rule' => unserialize($rejectReason->getRules())]
        ]);
    }

    public function getAdPreviewAction()
    {
        $vkAdId = (int) $this->params()->fromRoute('vk_account_id', 0);
        if (!$vkAdId) {
            return new JsonModel([
                "data" => [],
            ]);
        }
        return new JsonModel(['html' => $this->getServiceLocator()->get('Socnet\Logic\VkLogic')->getAdPreviewContent($vkAdId)]);
    }

    public function updateGeoDatabaseAction()
    {
        $regionsIds = $citiesIds = [];
        $vkApi = Vk\Core::getInstance();

        $countriesEntity = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCountries')->findAll();

        $query = $this->getObjectManager()->createQuery('SELECT u FROM \Socnet\Entity\VkRegions u');
        $regionsEntityAr = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        if ($regionsEntityAr) {
            $regionsIds = array_column($regionsEntityAr, 'id');
        }

        $query = $this->getObjectManager()->createQuery('SELECT u FROM \Socnet\Entity\VkCities u');
        $citiesEntityAr = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        if ($citiesEntityAr) {
            $citiesIds = array_column($citiesEntityAr, 'id');
        }

        foreach ($countriesEntity as $n => $country) {
            $regionsInVk = $vkApi->request('database.getRegions', ['country_id' => $country->getId(), 'count' => 1000])->getResponse();
            if ($regionsInVk) {
                foreach ($regionsInVk as $region) {
                    if (in_array($region->region_id, $regionsIds)) {
                        continue;
                    }
                    $regionEntity = new \Socnet\Entity\VkRegions();
                    $regionEntity->setId($region->region_id);
                    $regionEntity->setName($region->title);
                    $regionEntity->setCountryId($country->getId());
                    $this->getObjectManager()->persist($regionEntity);
                }
            }

            $citiesInVk = $vkApi->request('database.getCities', ['country_id' => $country->getId(), 'count' => 1000])->getResponse();
            if ($citiesInVk) {
                foreach ($citiesInVk as $city) {
                    if (in_array($city->cid, $citiesIds)) {
                        continue;
                    }
                    $cityEntity = new \Socnet\Entity\VkCities();
                    $cityEntity->setId($city->cid);
                    $cityEntity->setName($city->title);
                    $cityEntity->setCountryId($country->getId());
                    if (isset($city->important)) {
                        $cityEntity->setImportant($city->important);
                    }
                    $this->getObjectManager()->persist($cityEntity);
                }
            }
            if ($n && $n % 2 !== 0) {
            //    sleep(1);
            }
        }
        $this->getObjectManager()->flush();
        return false;
    }

    public function accountDeleteAction()
    {
        /**@var $campaign VkCampaigns*/
        /**@var $vkAd VkAds*/
        /**@var $vkAccount VkAccounts*/
        /**@var $cabinet Cabinets*/

        $vkAccountId = (int) $this->params()->fromRoute('vk_account_id', 0);
        if (!$vkAccountId) {return false;}

        $vkAccount = $this->vkAccountsRepository->findBy(['id' => $vkAccountId]);
        $vkAccount = reset($vkAccount);
        $this->getObjectManager()->remove($vkAccount);

        $allCabinets = $this->cabinetsRepository->findBy(['vk_account_id' => $vkAccountId]);
        $allClients = $this->getObjectManager()->getRepository('\Socnet\Entity\VkClients')->findBy(['bidder_account_id' => $vkAccountId]);
        $campaigns = $this->campaignsRepository->findBy(['vk_account_id' => $vkAccountId]);
        foreach ($campaigns as $campaign) {
            $vkAds = $this->vkAdsRepository->findBy(['campaign_id' => $campaign->getId()]);
            foreach ($vkAds as $vkAd) {
                $this->getObjectManager()->remove($vkAd);
            }
            $this->getObjectManager()->remove($campaign);
        }
        foreach ($allClients as $allClient) {
            $this->getObjectManager()->remove($allClient);
        }
        foreach ($allCabinets as $allCabinet) {
            $this->getObjectManager()->remove($allCabinet);
        }

        $this->getObjectManager()->flush();

        return $this->redirect()->toRoute('home');
    }

    private static function log($filename, $data)
    {
        $resource = fopen("var/log/".$filename.".txt", "a");

        $data = (array) $data;

        $resultString = date('Y-m-d H:i:s');

        foreach ($data as $field => $value) {
            $resultString .= ' ' . $field . ': ' . $value;
        }

        fwrite($resource, $resultString);
        fwrite($resource, PHP_EOL);
        fwrite($resource, '--------------------------------------------------');
        fwrite($resource, PHP_EOL);
        fclose($resource);
    }

    private static function log2($filename, $data)
    {
        $resource = fopen("var/log/".$filename.".txt", "a");

        $data = var_export($data, true);

        fwrite($resource, $data);
        fwrite($resource, PHP_EOL);
        fwrite($resource, '--------------------------------------------------');
        fwrite($resource, PHP_EOL);
        fclose($resource);
    }

    private function getAdsStatistic(array $ads)
    {
        $vkAdStatistic = [];
        foreach ($ads as $accessToken => $cabinetsId) {
            foreach ($cabinetsId as $cabinetId => $vkAds) {
                $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($accessToken);
                $vkAdStatistic[$cabinetId][] = $vkApi->request('ads.getStatistics', ['account_id' => $cabinetId, 'ids_type' => 'ad', 'ids' => implode(',', array_keys($vkAds)), 'period' => 'day', 'date_from' => date('Y-m-d'), 'date_to' => 0])->getResponse();
            }
        }
        return($vkAdStatistic);
    }

    protected function getObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->_objectManager;
    }
}