<?php

namespace ZF2DoctrineSQLLogger;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Types\Type;
use Zend\Log\Logger;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class ZF2DoctrineSQLLogger extends DebugStack implements ServiceLocatorAwareInterface
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var AbstractPlatform
     */
    protected $databasePlatform;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Priority for logging sql queries
     * @var int
     */
    protected $priority;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * @return AbstractPlatform
     */
    protected function getDatabasePlatform()
    {
        if (null === $this->databasePlatform) {
            $entityManager = $this->getServiceLocator()->get($this->config['entitymanager']);
            $this->databasePlatform = $entityManager->getConnection()->getDatabasePlatform();
        }
        return $this->databasePlatform;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        if (null === $this->priority) {
            $this->priority = $this->config['priority'];
        }
        return $this->priority;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = $this->getServiceLocator()->get($this->config['logger']);
        }
        return $this->logger;
    }

    /**
     * @param $types mixed
     * @param $key mixed
     * @param $param mixed
     * @return Type|null
     */
    protected function mapType($types, $key, $param)
    {
        // map type name by doctrine types map
        $name = $this->mapByTypesMap($types, $key);

        // map type name for known numbers
        if (is_null($name)) {
            $name = $this->mapByKeyNumber($key);
        }

        // map type name for known param type
        if (is_null($name)) {
            $name = $this->mapByParamType($param);
        }

        // if type could not be mapped, return null
        if (is_null($name)) {
            return null;
        }

        return Type::getType($name);
    }

    /**
     * Map by Doctrine DBAL types map
     * @param $types
     * @param $key
     * @return null|string
     */
    protected function mapByTypesMap($types, $key)
    {
        $typesMap = Type::getTypesMap();
        if (array_key_exists($key, $types) && array_key_exists($types[$key], $typesMap)) {
            $name = $types[$key];
        } else {
            $name = null;
        }
        return $name;
    }

    /**
     * @param $key
     * @return null|string
     */
    protected function mapByKeyNumber($key)
    {
        switch($key) {
            case 2:
                $name = Type::STRING;
                break;
            case 102:
                $name = Type::SIMPLE_ARRAY;
                break;
            default:
                $name = null;
                break;
        }
        return $name;
    }

    /**
     * @param $param
     * @return null|string
     */
    protected function mapByParamType($param)
    {
        switch(gettype($param)) {
            case 'array':
                $name = Type::SIMPLE_ARRAY;
                break;
            case 'string':
                $name = Type::STRING;
                break;
            case 'integer':
                $name = Type::INTEGER;
                break;
            default:
                $name = null;
                break;
        }
        return $name;
    }

    /**
     * @param $type Type
     * @param $value mixed
     * @return mixed
     */
    protected function prepareValue($type, $value)
    {
        if (is_object($type)) {
            switch(get_class($type)) {
                case 'Doctrine\DBAL\Types\SimpleArrayType':
                    break;
                default:
                    $value = var_export($value, true);
                    break;
            }
        } else {
            $value = var_export($value, true);
        }
        return $value;
    }

    /**
     * @param $message
     * @param array $extra
     */
    protected function log($message, $extra = array())
    {
        $this->getLogger()->log($this->getPriority(), $message, $extra);
    }

    /**
     * @param string $sql
     * @param array $params
     * @param array $types
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $assembled = $sql;
        if(!empty($params)) {
            foreach ($params as $key => $param) {
                $type = $this->mapType($types, $key, $param);
                if (null === $type) {
                    $this->log('Param could not be prepared: key: "' . $key
                        . '", value "' .  var_export($param, true) . '"!');
                    $assembled = join('?', explode('?', $assembled, 2));
                } else {
                    $value = $type->convertToDatabaseValue($param, $this->getDatabasePlatform());
                    $assembled = join($this->prepareValue($type, $value), explode('?', $assembled, 2));
                }
            }
        }
        $this->log($assembled);
        parent::startQuery($sql, $params, $types);
    }

    public function stopQuery()
    {
        parent::stopQuery();
        if ((bool)$this->config['log_executiontime']) {
            $this->log($this->queries[$this->currentQuery]['executionMS']);
        }
    }

}