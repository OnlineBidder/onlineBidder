<?php

namespace Socnet\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Socnet\Entity\PhotoStorage;
use Socnet\Entity\VkCountries;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

use getjump\Vk;


class UploaderController extends AbstractActionController
{
    protected $_objectManager;

    public function indexAction()
    {
        $countries = 0;
    }

    public function addAction()
    {
        $storeFolder = '/var/www/html/bidder/public/uploads/';
        $vkPhotoData = $targetFile = null;
        if (!empty($_FILES)) {
            $tempFile = $_FILES['file']['tmp_name'];
            $targetFile =  $storeFolder. $_FILES['file']['name'];
            if (move_uploaded_file($tempFile, $targetFile)) {
                $vkPhotoData = $this->getVkPhotoData($targetFile);
            }
        }
        if ($vkPhotoData) {
            $this->saveVkPhotoData($_FILES['file']['name'], $vkPhotoData);
            return false;
        }
        throw new \Exception();
    }

    public function getVkCountriesAction()
    {
        $post = $this->getRequest()->getPost();
        $params = $post['params'];
        $result = [];
        
        $countriesEntity = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCountries');
        if ($params && isset($params['all_countries']) && $params['all_countries']) {
            $countries = $countriesEntity->findAll();
        } else {
            $countries = $countriesEntity->findBy(['id' => range(1, 18)]);
        }
        foreach ($countries as $country) {
            $result[] = ['name' => $country->getName(), 'id' => $country->getId()];
        }
        return new JsonModel(['countries' => $result]);
    }

    public function getVkCitiesAction()
    {
        $post = $this->getRequest()->getPost();
        $cities = $result = [];
        $params = $post['params'];
        $citiesEntity = $this->getObjectManager()->getRepository('\Socnet\Entity\VkCities');
        if ($params && isset($params['country_id']) && ($countryId = $params['country_id'])) {
            $cities = $citiesEntity->findBy(['country_id' => $countryId]);
        }
        foreach ($cities as $city) {
            $result[] = ['name' => $city->getName(), 'id' => $city->getId()];
        }
        return new JsonModel(['cities' => $result]);
    }

    private function saveVkPhotoData($fileName, $data)
    {
        $photoStorageEntity = $this->getObjectManager()->getRepository('\Socnet\Entity\PhotoStorage')->findBy(['file_name' => $fileName]);
        if ($photoStorageEntity = reset($photoStorageEntity)) {
            $photoStorageEntity->setPhotoData(serialize($data));
        } else {
            $photoStorageEntity = new PhotoStorage();
            $photoStorageEntity->setFileName($fileName);
            $photoStorageEntity->setPhotoData(serialize($data));
        }
        $this->getObjectManager()->persist($photoStorageEntity);
        $this->getObjectManager()->flush();
    }

    private function getVkPhotoData($fileName)
    {
        $vkApi = Vk\Core::getInstance()->apiVersion('5.5')->setToken('b4320e4b95f057e4779fc2eb5b56f0d39366ace73099cde01515bf02c3869c1d1c70437d80ae61edad3a9');
        $vkPhotoUploadURL = $vkApi->request('ads.getUploadURL', ['ad_format' => 2])->getResponse();
        if ($vkPhotoUploadURL) {
            $guzzle = new \GuzzleHttp\Client();
            return $guzzle->post($vkPhotoUploadURL, [
                'body' => ['file' => fopen($fileName, 'r')]
            ])->json(['object' => true]);
        }
        return false;
    }

    protected function getObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->_objectManager;
    }
}