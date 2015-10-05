<?php
namespace StatsModule\Logic;

use Socnet\Logic\VkLogic;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\ResultSet\ResultSet;

class StatsLogic implements ServiceLocatorAwareInterface
{
    const VERY_BIG_VALUE = 999999999;

    const STATE_STOPPED = 0;
    const STATE_RUN     = 1;
    const STATE_REMOVED = 2;

    const APPROVE_STATUS_NEW        = 0;
    const APPROVE_STATUS_IN_PROCESS = 1;
    const APPROVE_STATUS_APPROVED   = 2;
    const APPROVE_STATUS_DECLINED   = 3;

    protected $_serviceLocator;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->_serviceLocator = $serviceLocator;
    }

    /**
     * Retrieve serviceManager instance
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->_serviceLocator;
    }

    public function getData($userId, $fromTime, $toTime)
    {
        return [
            'vkontakte' => $this->_getDataTree($userId, $fromTime, $toTime)
        ];
    }

    public function getPlainData($userId, $campaignsIds = 'all', $statuses, $fromTime, $toTime)
    {
        $allUserCampaignsIds = $this->getUserCampaignsIds($userId);
        if ($campaignsIds === 'all') {
            $campaignsIds = $allUserCampaignsIds;
        } else {
            $campaignsIds = array_map('intval', $campaignsIds);
            $campaignsIds = array_intersect($allUserCampaignsIds, $campaignsIds);
        }

        $ads = $this->_getAds($campaignsIds, $fromTime, $toTime, $statuses);

        return $ads;
    }

    public function getUserCampaignsIds($userId)
    {
        $campaignsIds = [];

        foreach ($this->_getCabinets($userId) as $cabinetId => $cabinetData) {
            foreach (array_keys($this->_getCampaigns($cabinetData['vk_account_id'], $cabinetId)) as $campaignId) {
                $campaignsIds[] = $campaignId;
            }
        }

        return $campaignsIds;
    }

    public function getCampaignsTree($userId)
    {
        $dataTree = [];
        $vkCabinets = $this->_getAllUserCabinets($userId);

        if (!$vkCabinets) {
            return $dataTree;
        }
        foreach ($vkCabinets as $vkCabinet) {

            foreach ($this->_getVkClients($vkCabinet) as $vkClient) {
                $cabinetName = $vkClient->getName();
                $campaigns = [];

                foreach ($this->_getCampaignsByClient($vkClient) as $campaignId => $campaignName) {
                    $campaigns[] = [
                        'id'         => $campaignId,
                        'name'       => mb_substr($campaignName, 0, 30, 'UTF-8'),
                    ];
                }

                $dataTree[] = [
                    'id'         => $vkCabinet->getId(),
                    'name'       => $cabinetName,
                    'childNodes' => $campaigns,
                ];
            }

        }

        return $dataTree;
    }

    private function _getDataTree($userId, $fromTime, $toTime)
    {
        $dataTree = [];

        foreach ($this->_getCabinets($userId) as $cabinetId => $cabinetData) {
            $cabinetName = $cabinetData['title'];
            $campaigns = [];

            foreach ($this->_getCampaigns($cabinetData['vk_account_id'], $cabinetId) as $campaignId => $campaignName) {
                $campaigns[] = [
                    'id'         => $campaignId,
                    'name'       => $campaignName,
                    'childNodes' => $this->_getAds($campaignId, $fromTime, $toTime),
                ];
            }

            $dataTree[] = [
                'id'         => $cabinetId,
                'name'       => $cabinetName,
                'childNodes' => $campaigns,
            ];
        }

        return $dataTree;
    }

    private function _getCabinets($userId)
    {
        $data = [];
        $objManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $vkAccountIds = [];
        $vkAccounts = $objManager->getRepository('\Socnet\Entity\VkAccounts')->findBy(['client_id' => $userId]);
        if (!empty($vkAccounts)) {
            foreach ($vkAccounts as $vkAccount) {
                $vkAccountIds[] = $vkAccount->getId();
            }
        }

        if (!empty($vkAccountIds)) {
            $cabinets = $objManager->getRepository('\Socnet\Entity\Cabinets')->findBy(['vk_account_id' => $vkAccountIds, 'account_status' => 1]);

            if (!empty($cabinets)) {
                foreach ($cabinets as $cabinet) {
                    $cabinetAccountId = $cabinet->getAccountId();
                    $extraInfo = $objManager->getRepository('\Socnet\Entity\VkClients')->findBy([
                        'account_id'        => $cabinetAccountId,
                        'bidder_account_id' => $cabinet->getVkAccountId(),
                    ]);
                    $data[$cabinetAccountId] = [
                        'vk_account_id' => $cabinet->getVkAccountId(),
                    ];
                    if ($extraInfo) {
                        $data[$cabinetAccountId]['title'] = $extraInfo[0]->getName();
                    } else {
                        $data[$cabinetAccountId]['title'] = '???';
                    }
                }
            }
        }

        return $data;
    }

    private function _getAllUserCabinets($userId)
    {
        $cabinets = [];
        $objManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $vkAccountIds = [];
        $vkAccounts = $objManager->getRepository('\Socnet\Entity\VkAccounts')->findBy(['client_id' => $userId]);
        if (!empty($vkAccounts)) {
            foreach ($vkAccounts as $vkAccount) {
                $vkAccountIds[] = $vkAccount->getId();
            }
        }

        if (!empty($vkAccountIds)) {
            $cabinets = $objManager->getRepository('\Socnet\Entity\Cabinets')->findBy(['vk_account_id' => $vkAccountIds, 'account_status' => 1]);
            return $cabinets;
        }
        return $cabinets;

    }

    private function _getVkClients($vkCabinet)
    {
        /**@var $vkCabinet \Socnet\Entity\Cabinets*/
        /**@var $vkAccount \Socnet\Entity\VkAccounts*/
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository('\Socnet\Entity\VkClients')->findBy(['bidder_account_id' => $vkCabinet->getVkAccountId(), 'account_id' => $vkCabinet->getAccountId()]);
    }

    private function _getCampaigns($vkAccountId, $cabinetId)
    {
        $data = [];
        $list = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository('\Socnet\Entity\VkCampaigns')->findBy(
            ['cabinet_id' => $cabinetId, 'vk_account_id' => $vkAccountId, 'status' => [0, 1]],
            ['id' => 'DESC']
        );

        if (!empty($list)) {
            foreach ($list as $campaign) {
                $data[$campaign->getId()] = $campaign->getName();
            }
        }

        return $data;
    }

    private function _getCampaignsByClient($vkClient)
    {
        $data = [];
        $list = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository('\Socnet\Entity\VkCampaigns')->findBy(
            ['vk_client_id' => $vkClient->getId(), 'status' => [0, 1]],
            ['id' => 'DESC']
        );
        foreach ($list as $campaign) {
            $data[$campaign->getId()] = $campaign->getName();
        }
        return $data;
    }


    /**
     * @param $campaignId
     * @param $fromTime
     * @param $toTime
     * @param array $statuses
     * @return array
     */
    private function _getAds(
        $campaignId,
        $fromTime,
        $toTime,
        $statuses = [
            VkLogic::AD_STATUS_STOP,
            VkLogic::AD_STATUS_PLAY,
            VkLogic::AD_STATUS_DELETE
        ]
    ) {
        $data = [];
        $filter = [
            'campaign_id' => $campaignId,
            'status'      => [],
        ];

        foreach ($statuses as $status) {
            if (!in_array((int) $status, [VkLogic::AD_STATUS_STOP, VkLogic::AD_STATUS_PLAY, VkLogic::AD_STATUS_DELETE], true)) {
                throw new \Exception();
            }

            $filter['status'][] = (int) $status;
        }

        $list = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository('\Socnet\Entity\VkAds')->findBy(
            $filter,
            ['id' => 'DESC']
        );

        $adIds = [];
        if (!empty($list)) {
            foreach ($list as $item) {
                $adId = $item->getId();
                $adIds[] = $adId;

                list($income, $count) = $this->_getIncome($adId, $fromTime, $toTime);

                $data[$adId] = [
                    'id'          => $adId,
                    'name'        => $item->getName(),
                    'income'      => $income,
                    'leadsCount'  => $count,
                    'impressions' => 0,     // can be set below
                    'clicks'      => 0,     // can be set below
                    'spent'       => 0,     // can be set below
                    'CPL'         => self::VERY_BIG_VALUE,
                    'CPC'         => self::VERY_BIG_VALUE,     // цена за клик, can be set below
                    'ROI'         => 0,     // can be set below
                    'CTR'         => 0,     // can be set below
                    'gain'        => $income,     // операционная прибыль, расходы (если есть) будут учтены ниже
                    'ROTA'        => 0,     // TODO: calc ROTA
                    'object'      => $item
                ];
            }
        }

        $offset = 0;
        while(true) {
            $requestIds = array_slice($adIds, $offset, 500);
            if (empty($requestIds)) {
                break;
            }

            $offset += 500;

            /** @var \StatsModule\Logic\StatsLogger $rc */
            $rc = $this->getServiceLocator()->get('StatsModule\Logic\StatsLogger')->getVkSummaryData($requestIds, $fromTime, $toTime);
            foreach ($rc as $row) {
                $data[$row->ad_id]['impressions'] = $row->impressions;
                $data[$row->ad_id]['clicks'] = $row->clicks;
                $data[$row->ad_id]['spent'] = $row->spent;
                $data[$row->ad_id]['gain'] = $data[$row->ad_id]['income'] - $row->spent;

                if ($data[$row->ad_id]['spent'] > 0) {
                    $data[$row->ad_id]['ROI'] = $data[$row->ad_id]['gain'] / $row->spent;
                }

                if ($row->clicks > 0) {
                    $data[$row->ad_id]['CPC'] = $row->spent / $row->clicks;
                }

                if ($data[$row->ad_id]['leadsCount'] > 0) {
                    $data[$row->ad_id]['CPL'] = $row->spent / $data[$row->ad_id]['leadsCount'];
                }

                if ($row->impressions > 0) {
                    $data[$row->ad_id]['CTR'] = $row->clicks / $row->impressions;
                }
            }
        }

        return $data;
    }

    private function _getIncome($adId, $fromTime, $toTime)
    {
        $adId     = (int) $adId;
        $fromTime = (int) $fromTime;
        $toTime   = (int) $toTime;

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $rc = $dbAdapter->query(
            "SELECT SUM(payment) as income, COUNT(payment) as cnt
             FROM postback
             WHERE ad_id = $adId AND (insert_time BETWEEN $fromTime and $toTime)
            ",
            $dbAdapter::QUERY_MODE_EXECUTE
        );

        $list = new ResultSet;
        $list->initialize($rc);

        $row = $list->toArray()[0];

        return [(float) $row['income'], (int) $row['cnt']];
    }
}
