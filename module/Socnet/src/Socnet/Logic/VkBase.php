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

class VkBase implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $_serviceLocator;
    protected $_objectManager;


    public function getAccounts()
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts')->findAll();
    }

    public function getAccountsByUser($userId)
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts')->findBy(['client_id' => $userId]);
    }

    public function getCabinets()
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\Cabinets')->findAll();
    }

    public function getCabinetsByUser($userId)
    {
        $vkAccount = $this->getAccountsByUser($userId);
        $vkAccount = reset($vkAccount);
        if (!$vkAccount) {
            return [];
        }
        return $this->getObjectManager()->getRepository('\Socnet\Entity\Cabinets')->findBy(['vk_account_id' => $vkAccount->getId(), 'account_status' => 1]);
    }

    public function getClients()
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkClients')->findAll();
    }

    public function getClientsByUser($userId)
    {
        $vkAccount = $this->getAccountsByUser($userId);
        $vkAccount = reset($vkAccount);
        if (!$vkAccount) {
            return [];
        }
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkClients')->findBy(['bidder_account_id' => $vkAccount->getId()]);
    }

    public function getCampaigns()
    {
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns')->findAll();
    }

    public function getCampaignsByUser($userId)
    {
        /**@var $vkCabinet Cabinets*/
        /**@var $vkAccount VkAccounts*/
        /**@var $vkClient VkClients*/

        $vkAccount = $this->getAccountsByUser($userId);
        $vkAccount = reset($vkAccount);
        if (!$vkAccount) {
            return [];
        }
        return $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns')->findBy(['vk_account_id' => $vkAccount->getId()]);
    }








    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->_serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->_serviceLocator;
    }

    protected function getObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->_objectManager;
    }
}
