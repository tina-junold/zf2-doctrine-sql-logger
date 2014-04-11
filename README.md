ZF2DoctrineSQLLogger
====================

Logs Doctrine DBAL queries as plain SQL with a Zend\Log\Logger.

Usage
-----

1. Add to require list in composer.json file (and of cause execute `composer.phar update`)

        "tburschka/zf2-doctrine-sql-logger": "dev-master"

2. Add to modules list in application.config.php

        'ZF2DoctrineSQLLogger'

3. Create a new Zend\Log\Logger or use an existing one

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

4. Configure the Z2fDoctrineSQLLogger

        [...]
        'ZF2DoctrineSQLLogger' => array(
            'entitymanager'     => 'doctrine.entitymanager.orm_default',
            'logger'            => 'doctrine.sql_logger',
            'priority'          => Zend\Log\Logger::NOTICE,
            'log_executiontime' => false,
        ),
        [...]

5. Enable logger for doctrine

        [...]
        'doctrine' => array(
            'configuration' => array(
                'orm_default' => array(
                    'sql_logger' => 'ZF2DoctrineSQLLogger',
                ),
            ),
        ),
        [...]
