<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'ZF2DoctrineSQLLogger' => function ($sm) {
                $config = $sm->get('Config');
                $config = $config['ZF2DoctrineSQLLogger'];
                $zf2dsl = new ZF2DoctrineSQLLogger\ZF2DoctrineSQLLogger($config);
                $zf2dsl->setServiceLocator($sm);
                return $zf2dsl;
            },
        ),
    ),
);
