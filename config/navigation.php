<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Navigation config
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
return [
    'item' => [

        // Hide from front menu
        'front' => false,

        // Default admin navigation
        'admin' => [
            'list'        => [
                'label'      => _t('Resource list'),
                'route'      => 'admin',
                'controller' => 'list',
                'action'     => 'index',
                'params'     => [
                    'all' => 1,
                ],
                'permission' => [
                    'resource' => 'list',
                ],

                'pages' => [
                    'all'    => [
                        'label'      => _t('All resources'),
                        'route'      => 'admin',
                        'controller' => 'list',
                        'action'     => 'index',
                        'params'     => [
                            'all' => 1,
                        ],
                    ],
                    'delete' => [
                        'label'      => _t('Deleted resources'),
                        'route'      => 'admin',
                        'controller' => 'list',
                        'action'     => 'index',
                        'params'     => [
                            'delete' => 1,
                        ],
                    ],
                    'orphan' => [
                        'label'      => _t('Orphan resources'),
                        'route'      => 'admin',
                        'controller' => 'list',
                        'action'     => 'index',
                        'params'     => [
                            'orphan' => 1,
                        ],
                    ],
                    'edit'   => [
                        'label'      => _t('Edit'),
                        'route'      => 'admin',
                        'controller' => 'media',
                        'action'     => 'edit',
                        'visible'    => 0,
                    ],
                    'attach' => [
                        'label'      => _t('Attach new media'),
                        'route'      => 'admin',
                        'controller' => 'list',
                        'action'     => 'attach',
                    ],
                ],
            ],
            'application' => [
                'label'      => _t('Application list'),
                'route'      => 'admin',
                'controller' => 'application',
                'action'     => 'list',
                'permission' => [
                    'resource' => 'application',
                ],

                'pages' => [
                    'list' => [
                        'label'      => _t('List'),
                        'route'      => 'admin',
                        'controller' => 'application',
                        'action'     => 'list',
                    ],
                    'add'  => [
                        'label'      => _t('Add'),
                        'route'      => 'admin',
                        'controller' => 'application',
                        'action'     => 'add',
                        'visible'    => 0,
                    ],
                    'edit' => [
                        'label'      => _t('Edit'),
                        'route'      => 'admin',
                        'controller' => 'application',
                        'action'     => 'edit',
                        'visible'    => 0,
                    ],
                ],
            ],
            'stats'       => [
                'label'      => _t('Statistics'),
                'route'      => 'admin',
                'controller' => 'stats',
                'action'     => 'index',
                'permission' => [
                    'resource' => 'stats',
                ],
            ],
            'test'        => [
                'label'      => _t('Test'),
                'route'      => 'admin',
                'controller' => 'test',
                'action'     => 'index',
                'permission' => [
                    'resource' => 'test',
                ],
            ],
            'tools'       => [
                'label'      => _t('Tools'),
                'route'      => 'admin',
                'controller' => 'tools',
                'action'     => 'index',
                'permission' => [
                    'resource' => 'tools',
                ],
            ],
        ],
    ],
];
