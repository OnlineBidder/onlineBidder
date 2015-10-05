<?
return array(
    'controllers' => array(
        'invokables' => array(
            'Socnet\Controller\Socnet' => 'Socnet\Controller\SocnetController',
            'Socnet\Controller\Clients' => 'Socnet\Controller\ClientsController',
            'Socnet\Controller\Vk' => 'Socnet\Controller\VkController',
            'Socnet\Controller\Uploader' => 'Socnet\Controller\UploaderController',
            'Socnet\Controller\Dsonline' => 'Socnet\Controller\DsonlineController',
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'vk' => array(
                    'options' => array(
                        // add [ and ] if optional ( ex : [<doname>] )
                        'route' => 'adsManagerDaemon',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Socnet\Controller',
                            'controller' => 'Socnet\Controller\Vk',
                            'action' => 'adsManagerDaemon'
                        ),
                    ),
                ),
            )
        ),
        'doctrine' => array(
            'driver' => array(
                'socnet_entities' => array(
                    'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                    'cache' => 'array',
                    'paths' => array(__DIR__ . '/../src/Socnet/Entity')
                ),

                'orm_default' => array(
                    'drivers' => array(
                        'Socnet\Entity' => 'socnet_entities',
                    )
                )
            )
        ),
    ),
    'router' => array(
        'routes' => array(
            'socnet' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/socnet[/:action][/:vk_account_id][/:cabinet_id][/:client_id][/:campaign_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'vk_account_id'     => '[a-zA-Z0-9]+',
                        'cabinet_id'     => '[0-9]+',
                        'client_id'     => '[0-9]*',
                        'campaign_id'     => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Socnet\Controller\Socnet',
                        'action'     => 'index',
                    ),
                ),
            ),
            'stata' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/socnet/stata',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Socnet\Controller\Socnet',
                        'action'     => 'stata',
                    ),
                ),
            ),
            'home' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Socnet\Controller\Socnet',
                        'action'     => 'index',
                    ),
                ),
            ),
            'clients' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/clients[/:action][/:vk_account_id][/:cabinet_id][/:client_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'vk_account_id'     => '[0-9]+',
                        'cabinet_id'     => '[0-9]+',
                        'client_id'     => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Socnet\Controller\Clients',
                        'action'     => 'index',
                    ),
                ),
            ),
            'uploader' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/uploader[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Socnet\Controller\Uploader',
                        'action'     => 'index',
                    ),
                ),
            ),
            'vk' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/vk[/:action][/:vk_account_id][/:cabinet_id][/:client_id][/:campaign_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'vk_account_id'     => '[0-9]+',
                        'cabinet_id'     => '[0-9]+',
                        'client_id'     => '[0-9]*',
                        'campaign_id'     => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Socnet\Controller\Vk',
                        'action'     => 'adsManager',
                    ),
                ),
            ),
            'saveSettings' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/saveSettings',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Socnet\Controller\Socnet',
                        'action'     => 'saveSettings',
                    ),
                ),
            ),
            'adsControl' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/vk/adsControl',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Socnet\Controller\Vk',
                        'action'     => 'adsControl',
                    ),
                ),
            ),
            'dsonline' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/dsonline',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Socnet\Controller\Dsonline',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'album' => __DIR__ . '/../view',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            'socnet_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Socnet/Entity')
            ),

            'orm_default' => array(
                'drivers' => array(
                    'Socnet\Entity' => 'socnet_entities',
                )
            )
        )
    ),
    'translator' => array(
        'locale' => 'ru_RU',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'Socnet\Logic\VkLogic' => 'Socnet\Logic\VkLogic',
            'Socnet\VkBase' => 'Socnet\Logic\VkBase',
        ),
    ),
);