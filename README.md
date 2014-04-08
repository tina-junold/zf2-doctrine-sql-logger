ZF2DoctrineSQLLogger
====================

Logs Doctrine DBAL queries with a Zend Log.

Usage
-----
 
1. Add to modules list in application.config.php

        'ZF2DoctrineSQLLogger'

2. Create a new Zend\Log\Logger or use an existing one

        [...]
        'service_manager' => array(
            'factories' => array(
                'doctrine.sql_logger' => function () {
                    $writer = new Zend\Log\Writer\Stream('data/logger_doctrine_sql.log');
                    $logger = new Zend\Log\Logger();
                    $logger->addWriter($writer);
                    return $logger;
                },
            ),
        ),
        [...]

3. Configure the Z2fDoctrineSQLLogger

        [...]
        'ZF2DoctrineSQLLogger' => array(
            'entitymanager'     => 'doctrine.entitymanager.orm_default',
            'logger'            => 'doctrine.sql_logger',
            'priority'          => Zend\Log\Logger::NOTICE,
            'log_executiontime' => false,
        ),
        [...]

4. Enable logger for doctrine

        [...]
        'doctrine' => array(
            'configuration' => array(
                'orm_default' => array(
                    'sql_logger' => 'ZF2DoctrineSQLLogger',
                ),
            ),
        ),
        [...]
