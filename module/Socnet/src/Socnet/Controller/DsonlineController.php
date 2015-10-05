<?php

namespace Socnet\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class DsonlineController extends AbstractActionController
{
    protected $_objectManager;

    public function indexAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
           return new JsonModel(['Only POST!']);
        }
        if (!$request->isPost()) {
            return new JsonModel(['Empty POST!']);
        }
        $postData = $request->getPost();
        $resource = fopen("/var/log/dsonline.txt", "a");

        $data = var_export($postData, true);

        fwrite($resource, $data);
        fwrite($resource, PHP_EOL);
        fwrite($resource, '--------------------------------------------------');
        fwrite($resource, PHP_EOL);
        fclose($resource);
        return new JsonModel(['OK!']);
    }

    protected function getObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->_objectManager;
    }
}