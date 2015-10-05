<?php

namespace Socnet\Controller;

use Socnet\Entity\VkClients;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Socnet\Entity\VkAccounts;
use Socnet\Entity\VkAds;
use Socnet\Entity\VkCampaigns;
use Socnet\Entity\Cabinets;
use Socnet\Entity\Clients;
use Socnet\Entity\Postbacks;
use getjump\Vk;

class SocnetController extends AbstractActionController
{
    const VK_APP_ID = '4647896';
    const VK_APP_SCOPE = 'friends,ads,offline';
    const VK_APP_SECRET = 'insgagYu3BXakqTKuCCE';

    const TMP_POSTBACK_ID = '7c2ab528401522fad7c4e85e7ee3e3dc';
    const BASYROV_POSTBACK_ID = '9483hftl394';

    protected $_objectManager;

    public function stataAction()
    {
        $adId = $this->getRequest()->getQuery('adId');

        return new ViewModel(array('adId' => $adId));
    }

    public function indexAction()
    {
        $auth = Vk\Auth::getInstance();
        $auth->setAppId(self::VK_APP_ID)->setScope(self::VK_APP_SCOPE)->setSecret(self::VK_APP_SECRET)->setRedirectUri(strpos($_SERVER['SERVER_NAME'], 'bidderonline') === 0 || strpos($_SERVER['SERVER_NAME'], 'www.bidderonline') === 0 ? 'http://bidderonline.ru/socnet' : 'http://zf2-tutorial.localhost/socnet');

        $token = $auth->startCallback();
        if ($token && $token->token) {
            $vkAccountAlreadyExist = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts')->findBy(['vk_user_id' => $token->userId]);
            if (!$vkAccountAlreadyExist) {
                $vk = new VkAccounts();
                $vk->setAccessKey($token->token);
                $vk->setExpiresIn($token->expiresIn);
                $vk->setVkUserId($token->userId);
                $vk->setClientId($this->zfcUserAuthentication()->getIdentity()->getId());
                $name = $this->getVkUserName($token->userId, $token->token);

                $vk->setName($name['name']);
                $vk->setLastName($name['last_name']);

                $this->getObjectManager()->persist($vk);
                $this->getObjectManager()->flush();

                if ($vk->getId()) {
                    $this->importAction($vk->getId(), $token->userId);
                }
            } else {
                $vkAccountAlreadyExist = reset($vkAccountAlreadyExist);
                $vkAccountAlreadyExist->setAccessKey($token->token);
                $this->getObjectManager()->persist($vkAccountAlreadyExist);
                $this->getObjectManager()->flush();
            }
            return $this->redirect()->toRoute('socnet');
        }

        $bidderUserId = $this->zfcUserAuthentication()->getIdentity()->getId();
        if ($bidderUserId > 6) {
            $vkAccounts = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts')->findBy(['client_id' => $bidderUserId]);
        } else {
            $vkAccounts = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts')->findAll();
        }


        return new ViewModel(array('vkAccounts' => $vkAccounts, 'addUrl' => $auth->getUrl(), 'bidderUserId' => $bidderUserId));
    }

    public function infoAction()
    {
        /**@var $ad VkAds*/
        /**@var $campaign VkCampaigns*/

        $vkAccountId = (int) $this->params()->fromRoute('vk_account_id', 0);
        $cabinetId = (int) $this->params()->fromRoute('cabinet_id', 0);
        $vkClientId = (int) $this->params()->fromRoute('client_id', 0);
        $vkCampaignId = (int) $this->params()->fromRoute('campaign_id', 0);

        $campaignFilter = ['cabinet_id' => $cabinetId];
        if ($vkClientId) {
            $campaignFilter['vk_client_id'] = $vkClientId;
        }
        if ($vkCampaignId) {
            $campaignFilter['id'] = $vkCampaignId;
        }
        if ($vkAccountId && $vkAccountId > 4) {
            $campaignFilter['vk_account_id'] = $vkAccountId;
        }
        $campaigns = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns')->findBy($campaignFilter);

        $bidderAdsControl = $this->getRequest()->getPost('bidder_ads');
        $adsAlgorithm = $this->getRequest()->getPost('ad_algorithm');
        $adsMinCounter = $this->getRequest()->getPost('min_counter');
        $adsMinCtr = $this->getRequest()->getPost('min_ctr');
        $adsMinutes = $this->getRequest()->getPost('minutes');
        $adsCpc = $this->getRequest()->getPost('cpc');

        if ($campaigns && $this->request->isPost()) {
            foreach ($campaigns as $campaign) {
                $bidderControl = $this->getRequest()->getPost('bidder_campaigns');

                $companyUnderBidder = false;
                $campaign->setBidderControl(
                    $bidderControl &&
                    is_array($bidderControl) &&
                    ($companyUnderBidder = in_array($campaign->getId(), $bidderControl)) ?
                        1 : 0
                );

                if ($companyUnderBidder) {
                    $ads = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(array('campaign_id' => $campaign->getId()));
                    foreach ($ads as $ad) {
                        $ad->setBidderControl($bidderAdsControl && in_array($ad->getId(), $bidderAdsControl) ? 1 : 0);
                        $ad->setAlgorithm($adsAlgorithm && isset($adsAlgorithm[$ad->getId()]) ? $adsAlgorithm[$ad->getId()] : 1);
                        $ad->setMinCounter($adsMinCounter && isset($adsMinCounter[$ad->getId()]) ? $adsMinCounter[$ad->getId()] : 10000);
                        $ad->setMinCtr($adsMinCtr && isset($adsMinCtr[$ad->getId()]) ? $adsMinCtr[$ad->getId()] : 0.04);
                        $ad->setOffMinutes($adsMinutes && isset($adsMinutes[$ad->getId()]) ? $adsMinutes[$ad->getId()] : 2);
                        $ad->setDesireCpc($adsCpc && isset($adsCpc[$ad->getId()]) ? $adsCpc[$ad->getId()] : 0);
                    }
                }

                $this->getObjectManager()->flush();
            }
            $_POST = NULL;
            $routeParams = ['action' => 'info', 'vk_account_id' => $vkAccountId, 'cabinet_id' => $cabinetId];
            $routeParams['client_id'] = $vkClientId;
            $routeParams['campaign_id'] = $vkCampaignId;
            return $this->redirect()->toRoute('socnet', $routeParams);
        }

        if (!$campaigns) {
            $this->importCampaigns($cabinetId, $vkClientId, $vkAccountId);
            $campaigns = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns')->findBy($campaignFilter);
        }
        $vkAds = [];
            foreach ($campaigns as $campaign) {
                $vkAds[$campaign->getId()] = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(array('campaign_id' => $campaign->getId()), array('id' => 'DESC'));
            }

        $arViewParams = ['vkAds' => $vkAds, 'vkAccountId' => $vkAccountId, 'vkCampaigns' => $campaigns, 'cabinet_id' => $cabinetId];
        $arViewParams['vkClientId'] = $vkClientId;
        $arViewParams['campaignId'] = $vkCampaignId;
        return new ViewModel($arViewParams);
    }

    public function tempImportAction($id = 0, $vkUserId = 0)
    {
        return 1;
        $id = 1;
        $vkAcc = $this->getObjectManager()->find('\Socnet\Entity\VkAccounts', $id);
        $vkUserId = 277510247;

        $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($vkAcc->getAccessKey());
        usleep(550000);

        $vkAdAccounts = $vkApi->request('ads.getAccounts')->getResponse();
        usleep(550000);
        if ($this->saveCabinets($vkAdAccounts, $vkUserId, $id)) {
            foreach ($vkAdAccounts as $vkCabinet) {
                if ($vkCabinet->account_type === 'agency') {
                    $vkAgencyClients = $vkApi->request('ads.getClients', ['account_id' => $vkCabinet->account_id])->getResponse();
                    $this->saveVkClients($vkAgencyClients, $vkCabinet->account_id);
                    usleep(1000000);
                }
            }
        }

        return new ViewModel(array('vkAds' => []));
    }


    public function importAction($vkAccountId = 0, $vkUserId = 0)
    {
        $vkAccountId = $vkAccountId ? $vkAccountId :(int) $this->params()->fromRoute('vk_account_id', 0);
        $vkAcc = $this->getObjectManager()->find('\Socnet\Entity\VkAccounts', $vkAccountId);

        $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($vkAcc->getAccessKey());
        usleep(550000);

        $vkAdAccounts = $vkApi->request('ads.getAccounts')->getResponse();
        usleep(550000);

        if ($this->saveCabinets($vkAdAccounts, $vkUserId, $vkAccountId)) {
            foreach ($vkAdAccounts as $vkCabinet) {
                if ($vkCabinet->account_type === 'agency') {
                    $vkAgencyClients = $vkApi->request('ads.getClients', ['account_id' => $vkCabinet->account_id])->getResponse();
                    $this->saveVkClients($vkAgencyClients, $vkCabinet->account_id, $vkAccountId);
                    usleep(1000000);
                }
            }
        }
        return new ViewModel(array('vkAds' => []));
    }

    private function importCampaigns($cabinetId, $clientId = 0, $vkAccountId = 0)
    {
        /**@var $cabinet Cabinets*/

        $cabinet = $this->getObjectManager()->getRepository('\Socnet\Entity\Cabinets')->findBy(['account_id' => $cabinetId, 'vk_account_id' => $vkAccountId]);

        if ($cabinet && ($cabinet = reset($cabinet))) {
            $vkAccount = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAccounts')->findBy(['id' => $cabinet->getVkAccountId()]);
            $vkAccount = reset($vkAccount);
            $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($vkAccount->getAccessKey());

            $params = ['account_id' => $cabinetId];
            if ($clientId) $params['client_id'] = $clientId;
            $vkCampaigns = $vkApi->request('ads.getCampaigns', $params)->getResponse();
            $this->saveCampaigns($vkCampaigns, $cabinetId, $clientId);
            usleep(500000);
            $params = ['account_id' => $cabinetId, 'campaign_ids' => $vkCampaigns->id];
            if ($clientId) $params['client_id'] = $clientId;

            $vkAds = $vkApi->request('ads.getAds', $params)->getResponse();

            $this->saveAds($vkAds, $clientId);
            usleep(500000);
        }
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $user = $this->getObjectManager()->find('\Socnet\Entity\VkAccounts', $id);

        if ($this->request->isPost()) {
            $user->setFullName($this->getRequest()->getPost('fullname'));

            $this->getObjectManager()->persist($user);
            $this->getObjectManager()->flush();

            return $this->redirect()->toRoute('home');
        }

        return new ViewModel(array('user' => $user));
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $user = $this->getObjectManager()->find('\Socnet\Entity\VkAccounts', $id);

        if ($this->request->isPost()) {
            $this->getObjectManager()->remove($user);
            $this->getObjectManager()->flush();

            return $this->redirect()->toRoute('home');
        }

        return new ViewModel(array('user' => $user));
    }

    private function saveCampaigns(array $campaigns, $cabinetId, $vkClientId = 0)
    {
        foreach ($campaigns as $campaign) {
            $campaignEntity = new VkCampaigns();
            $campaignEntity->setCabinetId($cabinetId);
            $campaignEntity->setVkClientId($vkClientId);
            foreach ($campaign as $key => $value) {
                $campaignEntity->set($key, $value);
            }

            $this->getObjectManager()->persist($campaignEntity);
        }
        $this->getObjectManager()->flush();

    }

    private function saveVkClients(array $clients, $cabinetId, $bidderAccountId)
    {
        foreach ($clients as $client) {
            $vkClient = $this->getObjectManager()->getRepository('\Socnet\Entity\VkClients')->findBy(['id' => $client->id]);
            if (!$vkClient) {
                $vkClientsEntity = new VkClients();
                $vkClientsEntity->setAccountId($cabinetId);
                $vkClientsEntity->setBidderAccountId($bidderAccountId);
                foreach ($client as $key => $value) {
                    $vkClientsEntity->set($key, $value);
                }

                $this->getObjectManager()->persist($vkClientsEntity);
            }
        }
        $this->getObjectManager()->flush();

    }

    private function saveAds(array $ads, $vkClientId = 0)
    {
        foreach ($ads as $ad) {
            $adsEntity = new VkAds();
            $adsEntity->setVkClientId($vkClientId);

            foreach ($ad as $key => $value) {
                $adsEntity->set($key, $value);
            }

            $this->getObjectManager()->persist($adsEntity);
        }
        $this->getObjectManager()->flush();
    }

    private function saveCabinets(array $cabinets, $vkUserId, $vkAccountId)
    {
        if (!$cabinets) {
            return false;
        }
        $cabinetEntity = false;
        foreach ($cabinets as $cabinet) {
            $vkCabinet = $this->getObjectManager()->getRepository('\Socnet\Entity\Cabinets')->findBy(['account_id' => $cabinet->account_id, 'vk_user_id' => $vkUserId]);
            if (!$vkCabinet) {
                $cabinetEntity = new Cabinets();
                foreach ($cabinet as $key => $value) {
                    $cabinetEntity->set($key, $value);
                }
                $cabinetEntity->setVkUserId($vkUserId);
                $cabinetEntity->setVkAccountId($vkAccountId);
                $this->getObjectManager()->persist($cabinetEntity);
            }
        }
        $this->getObjectManager()->flush();

        return $cabinetEntity->getId();
    }

    private function getVkUserName($userId, $token)
    {
        $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken($token);
        $name = $vkApi->request('users.get')->getResponse()[0];

        return array('name' => $name->first_name ? $name->first_name : '', 'last_name' => $name->last_name ? $name->last_name : '');
    }

    public function postbacksmsAction()
    {
# @to - номер получателя, например: 79221111111
# @msg - сообщение в кодировке windows-1251
# @login - логин на веб-сервисе websms.ru
# @password - пароль на веб-сервисе websms.ru
        $login = 'samktulho';
        $password = 's2a2m2';
        $to = '89523932926';
        $msg = 'Prodan chehol!';

        $u = 'http://www.websms.ru/http_in5.asp';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            'Http_username='.urlencode($login).'&Http_password='.urlencode($password).'&Phone_list='.$to.'&Message='.urlencode($msg));
        curl_setopt($ch, CURLOPT_URL, $u);
        $u = trim(curl_exec($ch));
        curl_close($ch);
        preg_match("/message_id\s*=\s*[0-9]+/i", $u, $arr_id );
        $id = preg_replace("/message_id\s*=\s*/i", "", @strval($arr_id[0]) );
        return $id;
    }

    public function postbackAction()
    {
        header('Access-Control-Allow-Origin: *');
        ini_set('ignore_user_abort', 1);
        ini_set('max_execution_time', 0);
        /**@var $ad VkAds*/
        $postbackId = $this->params()->fromRoute('vk_account_id', 0);
        $adId = (int) $this->getRequest()->getQuery('subId', 0);

        if (!$postbackId || !$adId || ($postbackId !== self::TMP_POSTBACK_ID && $postbackId !== self::BASYROV_POSTBACK_ID)) {echo 'MISSING OP INVALID PARAMETERS'; die;}

        $ad = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(array('id' => $adId));
        $payment = (double) $this->getRequest()->getQuery('payment', 0);
        $leadId = (string) $this->getRequest()->getQuery('leadId', null);

        if (!$ad || !($ad = reset($ad))) {
            $fp = fopen('/var/log/11111.txt', 'a');
            fwrite($fp, var_export('Update ADS!!! '.$adId, true));
            fwrite($fp, var_export(PHP_EOL, true));
            fclose($fp);
        } else {
            $postbackEntity = new Postbacks();
            $postbackEntity->setAdId($ad->getId());
            $postbackEntity->setInsertTime(time());
            $postbackEntity->setPayment($payment);
            if ($leadId) {
                $postbackEntity->setLeadId($leadId);
            }
            $this->getObjectManager()->persist($postbackEntity);
            $this->getObjectManager()->flush();
        }


        $ch = curl_init('http://postback.plarin.net/96EwrBdvSDmH4wJhBjbQmmmV?g1=' . $adId . '&g1_value=' . $payment);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        if ($res !== '{"success": "POSTBACK ACCEPTED"}') {
            $fp = fopen('/var/log/11111.txt', 'a');
            fwrite($fp, var_export('Res: ' . $res, true));
            fwrite($fp, var_export(PHP_EOL, true));
            fwrite($fp, var_export('Error: ' . curl_error($ch), true));
            fwrite($fp, var_export(PHP_EOL, true));
            fwrite($fp, var_export('Http code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE), true));
            fwrite($fp, var_export(PHP_EOL, true));
            fclose($fp);
        }
        curl_close($ch);
        echo '{"success": "POSTBACK ACCEPTED"}';
        die;
    }

    public function pixelAction()
    {
        header("Content-type: image/jpg");
        ini_set('ignore_user_abort', 1);
        ini_set('max_execution_time', 0);
        echo readfile('http://bidderonline.ru/img/empty.gif');

        /**@var $ad VkAds*/
        $postbackId = $this->params()->fromRoute('vk_account_id', 0);
        $adId = (int) $this->getRequest()->getQuery('subId', 0);

        if (!$postbackId || !$adId || ($postbackId !== self::TMP_POSTBACK_ID && $postbackId !== self::BASYROV_POSTBACK_ID)) {echo 'MISSING OP INVALID PARAMETERS'; die;}

        $ad = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findBy(array('id' => $adId));
        $payment = (double) $this->getRequest()->getQuery('payment', 0);

        if (!$ad || !($ad = reset($ad))) {
            $fp = fopen('/var/log/11111.txt', 'a');
            fwrite($fp, var_export('Update ADS!!! '.$adId, true));
            fwrite($fp, var_export(PHP_EOL, true));
            fclose($fp);
        } else {
            $postbackEntity = new Postbacks();
            $postbackEntity->setAdId($ad->getId());
            $postbackEntity->setInsertTime(time());
            $postbackEntity->setPayment($payment);
            $this->getObjectManager()->persist($postbackEntity);
            $this->getObjectManager()->flush();
        }

        $ch = curl_init('http://postback.plarin.net/96EwrBdvSDmH4wJhBjbQmmmV?g1=' . $adId . '&g1_value=' . $payment);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        if ($res !== '{"success": "POSTBACK ACCEPTED"}') {
            $fp = fopen('/var/log/11111.txt', 'a');
            fwrite($fp, var_export('Res: ' . $res, true));
            fwrite($fp, var_export(PHP_EOL, true));
            fwrite($fp, var_export('Error: ' . curl_error($ch), true));
            fwrite($fp, var_export(PHP_EOL, true));
            fwrite($fp, var_export('Http code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE), true));
            fwrite($fp, var_export(PHP_EOL, true));
            fclose($fp);
        }
        curl_close($ch);
        die;
    }

    public function saveSettingsAction()
    {
        $params = $this->getRequest()->getPost('params', null);
        if (!$params || !is_array($params) || !isset($params['type']) || !isset($params['id'])) { echo 'Ошибка'; exit;}

        switch ($params['type']) {
            case 'campaign':
                $updateObject = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCampaigns')->findBy(['id' => $params['id']]);
                unset($params['type'], $params['id']);
                break;
            default:
                echo 'Неизвестный тип';
                exit;
        }
        foreach ($updateObject as $object) {
            foreach ($params as $field => $value) {
                $object->set($field, $value);
            }
        }

        $this->getObjectManager()->flush();
        echo 1;
        exit;
    }

    public function testAction()
    {
        $ad = $this->getObjectManager()->getRepository('\Socnet\Entity\VkAds')->findAll();
        var_dump($ad);
        return 'It\'s OK!';
        $postbackEntity = $this->getObjectManager()->getRepository('\Socnet\Entity\Postbacks')->findAll();

        foreach ($postbackEntity as $postback) {
            if ($postback->getId() < 24) {
                $ch = curl_init('http://postback.plarin.net/96EwrBdvSDmH4wJhBjbQmmmV?g1=' . $postback->getAdId() . '&g1_value=' . $postback->getPayment());

                curl_setopt($ch, CURLOPT_HEADER, 0);

                curl_exec($ch);
                curl_close($ch);
                sleep(1);
            }
        }
    }

    protected function getObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->_objectManager;
    }
}