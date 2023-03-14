<?php

namespace App\XMPie\uProduce;

use Cake\Core\Configure;
use XMPieWsdlClient\uProduceFactory;
use XMPieWsdlClient\XMPie\uProduce\v_12_0_1\Fabricator\RequestFabricator;
use XMPieWsdlClient\XMPie\uProduce\v_12_0_1\Fabricator\ServiceFabricator;

class BaseClient
{
    private array $xmpOptions;
    private array $soapOptions;
    private array $config;

    protected RequestFabricator $RequestFabricator;
    protected ServiceFabricator $ServiceFabricator;

    public function __construct()
    {
        $this->xmpOptions = Configure::read('XMPieClient.xmp_options');
        $this->soapOptions = Configure::read('XMPieClient.soap_options');
        $this->config = Configure::read('XMPieClient.config');

        $Factory = new uProduceFactory($this->xmpOptions, $this->soapOptions, $this->config);
        $this->RequestFabricator = $Factory->getUProduceRequestFabricator();
        $this->ServiceFabricator = $Factory->getUProduceServiceFabricator();
    }
}