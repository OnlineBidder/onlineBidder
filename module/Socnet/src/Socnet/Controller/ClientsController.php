<?php

namespace Socnet\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Socnet\Entity\Clients;
use Socnet\Entity\VkCampaigns;
use BjyAuthorize;


class ClientsController extends AbstractActionController
{
    protected $_objectManager;

    public function indexAction()
    {
        $clients = $this->getObjectManager()->getRepository('\Socnet\Entity\Clients')->findAll();

        return new ViewModel(array('users' => $clients));
    }

    public function cabinetsAction()
    {
        $vkClients = [];
        $bidderUserId = $this->zfcUserAuthentication()->getIdentity()->getId();

        $vkAccountId = (int) $this->params()->fromRoute('vk_account_id', 0); //bidder account ID
        $vkAccount = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts')->findBy(['id' => $vkAccountId]);
        $vkAccount = reset($vkAccount);
        if ($bidderUserId > 6 && $vkAccount->getClientId() != $bidderUserId) {
            throw new BjyAuthorize\Exception\UnAuthorizedException('��� �������');
        }

        /**@var $cabinets \Socnet\Entity\Cabinets*/
        $cabinets = $this->getObjectManager()->getRepository('\Socnet\Entity\Cabinets')->findBy(['vk_account_id' => $vkAccountId]);

        if ($cabinets && ($cabinet = reset($cabinets)) && $cabinet->getAccountType()) {
            foreach ($cabinets as $cabinet) {
                /**@var $cabinet \Socnet\Entity\Cabinets*/
                if ($cabinet->getAccountType() === 'agency') {
                    $vkClients[$cabinet->getAccountId()] = $this->getObjectManager()->getRepository('\Socnet\Entity\VkClients')->findBy(['account_id' => $cabinet->getAccountId(), 'bidder_account_id' => $vkAccountId]);
                }
            }
        }

        return new ViewModel(array('cabinets' => $cabinets, 'vkAccountId' => $vkAccountId, 'vkClients' => $vkClients));
    }

    public function campaignsAction()
    {
        /**@var $campaign VkCampaigns*/

        $vkAccountId = (int) $this->params()->fromRoute('vk_account_id', 0);
        $cabinetId = (int) $this->params()->fromRoute('cabinet_id', 0);
        if (!$cabinetId) {
            return $this->redirect()->toRoute('socnet', ['action' => 'info']);
        }
        $vkClientId = (int) $this->params()->fromRoute('client_id', 0);

        $campaignFilter = ['cabinet_id' => $cabinetId, 'status' => [0, 1]];
        if ($vkClientId) {
            $campaignFilter['vk_client_id'] = $vkClientId;
        }

        if ($vkAccountId && $vkAccountId > 4) {
            $campaignFilter['vk_account_id'] = $vkAccountId;
        }

        $campaigns = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns')->findBy($campaignFilter, array('id' => 'DESC'));

        foreach ($campaigns as $campaign) {
            $vkAds[$campaign->getId()] = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(array('campaign_id' => $campaign->getId()));
        }

        $arViewParams = ['vkAccountId' => $vkAccountId, 'vkCampaigns' => $campaigns, 'cabinetId' => $cabinetId];
        if ($vkClientId) $arViewParams['vkClientId'] = $vkClientId;

        return new ViewModel($arViewParams);

    }

    public function addAction()
    {
        if ($this->request->isPost()) {
            $user = new Clients();
            $user->setName($this->getRequest()->getPost('name'));
            $user->setLastName($this->getRequest()->getPost('last_name'));
            $user->setCompany($this->getRequest()->getPost('company'));

            $this->getObjectManager()->persist($user);
            $this->getObjectManager()->flush();
            $newId = $user->getId();

            return $this->redirect()->toRoute('home');
        }
        return new ViewModel();
    }

    protected function getObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->_objectManager;
    }
}