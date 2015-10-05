<?php

namespace Socnet\Services;
use Socnet\Entity\VkAds;
use Socnet\Entity\VkCampaigns;

use getjump\Vk;

class SocnetService
{
    protected $_objectManager;

    public function saveCampaigns(array $campaigns)
    {
        foreach ($campaigns as $campaign) {
            $campaignEntity = new VkCampaigns();
            $campaignEntity->setClientId(1);
            foreach ($campaign as $key => $value) {
                $campaignEntity->set($key, $value);
            }

            $this->getObjectManager()->persist($campaignEntity);
            $this->getObjectManager()->flush();
        }
    }

    public function saveAds(array $ads)
    {
        foreach ($ads as $ad) {
            $adsEntity = new VkAds();
            foreach ($ad as $key => $value) {
                $adsEntity->set($key, $value);
            }

            $this->getObjectManager()->persist($adsEntity);
            $this->getObjectManager()->flush();
        }
    }

    public function getVkUserName($userId, $token)
    {
        $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($token);
        $name = reset($vkApi->request('users.get')->getResponse());

        return array('name' => $name->first_name ? $name->first_name : '', 'last_name' => $name->last_name ? $name->last_name : '');
    }

    protected function getObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->_objectManager;
    }

}

