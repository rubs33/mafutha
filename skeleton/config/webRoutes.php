<?php
return [

    // Basic web route
    'helloworld' => [
        'type' => 'literal',
        'route' => '/helloworld',
        'defaults' => [
            'controller' => 'HelloWorld',
            'action' => 'show'
        ]
    ],

    // Default list
    'list' => [
        'type' => 'segment',
        'route' => '/<directory>(/list(/<page>))',
        'options' => array(
            'page' => '\d+'
        ),
        'defaults' => [
            'controller' => 'List',
            'action' => 'show',
            'page' => '1'
        ]
    ],

    // Default show
    'show' => [
        'type' => 'segment',
        'route' => '/<directory>/<id>(/show)',
        'options' => array(
            'id' => '\d+'
        ),
        'defaults' => [
            'controller' => 'Show',
            'action' => 'show'
        ]
    ],

    // Default insert
    'insert' => [
        'type' => 'segment',
        'route' => '/<directory>/insert(/<action>)',
        'options' => [
            'action' => 'form|save'
        ],
        'defaults' => [
            'controller' => 'Insert',
            'action' => 'form'
        ]
    ],

    // Default edit
    'edit' => [
        'type' => 'segment',
        'route' => '/<directory>/<id>/edit(/<action>)',
        'options' => [
            'id' => '\d+',
            'action' => 'form|save'
        ],
        'defaults' => [
            'controller' => 'Edit',
            'action' => 'form'
        ]
    ],

    'default_id' => [
        'type' => 'segment',
        'route' => '/<directory>/<controller>/<id>(/<action>)',
        'options' => [
            'id' => '\d+',
        ],
        'defaults' => [
            'action' => 'show'
        ]
    ],

    'default' => [
        'type' => 'segment',
        'route' => '/<directory>/<controller>(/<action>)',
        'defaults' => [
            'action' => 'show'
        ]
    ]
];