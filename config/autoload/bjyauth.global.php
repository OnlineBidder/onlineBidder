<?php
//return array();
return array(
    'bjyauthorize' => array(
        'default_role' => 'guest',
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',
        'authenticated_role' => 'user',
        'role_providers'        => array(
            // using an object repository (entity repository) to load all roles into our ACL
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
                'object_manager'    => 'doctrine.entitymanager.orm_default',
                'role_entity_class' => 'MyUser\Entity\Role',
            ),
        ),
        'guards' => array(
            /* If this guard is specified here (i.e. it is enabled), it will block
             * access to all controllers and actions unless they are specified here.
             * You may omit the 'action' index to allow access to the entire controller
             */
            'BjyAuthorize\Guard\Controller' => array(
                array(
                    'controller' => 'zfcuser',
                    'action' => array('index'),
                    'roles' => array('guest', 'user'),
                ),
                array(
                    'controller' => 'zfcuser',
                    'action' => array('login', 'authenticate', 'register'),
                    'roles' => array('guest'),
                ),
                array(
                    'controller' => 'zfcuser',
                    'action' => array('logout'),
                    'roles' => array('user'),

                ),
                array('controller' => 'Application\Controller\Index', 'roles' => array()),
                array(
                    'controller' => 'Socnet\Controller\Socnet',
                    'action' => array('index', 'view', 'stata', 'info'),
                    'roles' => array('user'),

                ),
                array(
                    'controller' => 'Socnet\Controller\Socnet',
                    'action' => array('tempImport', 'test'),
                    'roles' => array('administrator'),

                ),
                array(
                    'controller' => 'Socnet\Controller\Socnet',
                    'action' => array('postback', 'test', 'saveSettings', 'pixel', 'postbacksms'),
                    'roles' => array('guest'),

                ),
                array(
                    'controller' => 'Socnet\Controller\Clients',
                    'action' => array('index', 'cabinets', 'campaigns'),
                    'roles' => array('user'),

                ),
                array(
                    'controller' => 'Socnet\Controller\Clients',
                    'action' => array('add'),
                    'roles' => array('administrator'),

                ),
                array(
                    'controller' => 'Socnet\Controller\Uploader',
                    'action' => array('index', 'add', 'getVkCountries', 'getVkCities'),
                    'roles' => array('user'),

                ),
                array(
                    'controller' => 'Socnet\Controller\Vk',
                    'action' => array('adsManager', 'adsProcessing', 'adsChecker', 'adsManagerDaemon'),
                    'roles' => array('guest'),

                ),
                array(
                    'controller' => 'Socnet\Controller\Vk',
                    'action' => array('adsCheckerForce', 'test', 'accountDelete', 'updateGeoDatabase'),
                    'roles' => array('moderator'),

                ),
                array(
                    'controller' => 'Socnet\Controller\Vk',
                    'action' => array('doIt', 'getRejectReason', 'getAdPreview', 'adsControl'),
                    'roles' => array('user'),
                ),
                array(
                    'controller' => 'StatsModule\Controller\List',
                    'action' => array('index', 'ajax'),
                    'roles' => array('user'),
                ),
                array(
                    'controller' => 'MyBlog\Controller\BlogPost',
                    'action' => array('add', 'edit', 'delete'),
                    'roles' => array('administrator'),
                ),
                array(
                    'controller' => 'Socnet\Controller\Dsonline',
                    'action' => array('index'),
                    'roles' => array('guest'),
                ),
            ),
        ),
    ),
);