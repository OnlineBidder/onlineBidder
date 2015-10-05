<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'StatsModule\Controller\Stats' => 'StatsModule\Controller\StatsController',
            'StatsModule\Controller\List' => 'StatsModule\Controller\ListController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'stats' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/stats[/:controller[/:action]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'StatsModule\Controller',
                        'controller'    => 'List',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'StatsModule\Controller',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'StatsModule' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'StatsModule\Logic\StatsLogic'  => 'StatsModule\Logic\StatsLogic',
            'StatsModule\Logic\StatsLogger' => 'StatsModule\Logic\StatsLogger',
        ),
    ),
);
