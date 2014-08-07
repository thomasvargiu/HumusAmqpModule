<?php

namespace HumusAmqpModuleTest\Service;

use HumusAmqpModule\PluginManager\Connection as ConnectionPluginManager;
use HumusAmqpModule\PluginManager\RpcClient as RpcClientPluginManager;
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
use HumusAmqpModule\Service\RpcClientAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

class RpcClientAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var RpcClientAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
                    'rpc_client' => 'HumusAmqpModule\Amqp\RpcClient',
                ),
                'connections' => array(
                    'default' => array(
                        'host' => 'localhost',
                        'port' => 5672,
                        'user' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                        'lazy' => true
                    )
                ),
                'rpc_clients' => array(
                    'test-rpc-client' => array(
                        'expect_serialized_response' => true
                    )
                )
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $connectionManager = new ConnectionPluginManager());
        $connectionManager->addAbstractFactory($dependentComponent);
        $connectionManager->setServiceLocator($services);

        $components = $this->components = new RpcClientAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\RpcClient', $rpcClientManager = new RpcClientPluginManager());
        $rpcClientManager->addAbstractFactory($components);
        $rpcClientManager->setServiceLocator($services);
    }

    public function testCreateRpcClient()
    {
        $rpcClient = $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
        $this->assertInstanceOf('HumusAmqpModule\Amqp\RpcClient', $rpcClient);
        /* @var $rpcClient \HumusAmqpModule\Amqp\RpcClient */
        $this->assertEquals('direct', $rpcClient->getExchangeOptions()->getType());
    }

    public function testCreateRpcClientWithCustomClass()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['rpc_clients']['test-rpc-client']['class'] = __NAMESPACE__ . '\TestAsset\CustomRpcClient';
        $this->services->setService('Config', $config);

        $rpcClient = $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
        $this->assertInstanceOf(__NAMESPACE__ . '\TestAsset\CustomRpcClient', $rpcClient);
        /* @var $rpcClient \HumusAmqpModule\Amqp\RpcClient */
        $this->assertEquals('direct', $rpcClient->getExchangeOptions()->getType());
    }
}