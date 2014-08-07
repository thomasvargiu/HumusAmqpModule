<?php

namespace HumusAmqpModule\Service\Controller;

class ConsumerFactory extends AbstractConsumerFactory
{
    /**
     * @return string
     */
    protected function getConsumerType()
    {
        return 'HumusAmqpModule\\PluginManager\\Consumer';
    }

    /**
     * @return string
     */
    protected function getControllerClass()
    {
        return 'HumusAmqpModule\Controller\ConsumerController';
    }
}