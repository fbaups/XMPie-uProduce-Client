<?php

namespace App\XMPie\uProduce;

use Cake\Core\Configure;

class ClientFactory
{
    private array $xmpOptions;
    private array $soapOptions;
    private array $config;

    private ?AccountClient $AccountClient = null;
    private ?CampaignClient $CampaignClient = null;
    private ?PlanClient $PlanClient = null;
    private ?DataSourceClient $DataSourceClient = null;
    private ?DocumentClient $DocumentClient = null;
    private ?TempStorageClient $TempStorageClient = null;

    public function __construct(array $xmpOptions = [], array $soapOptions = [], array $config = [])
    {
        $this->setXmpOptions($xmpOptions, false);
        $this->setSoapOptions($soapOptions, false);
        $this->setConfig($config, false);

        $this->writeToConfiguration();
    }

    /**
     * @param array $xmpOptions
     * @param bool $writeToConfiguration
     * @return ClientFactory
     */
    public function setXmpOptions(array $xmpOptions, bool $writeToConfiguration = true): ClientFactory
    {
        $this->xmpOptions = $xmpOptions;
        if ($writeToConfiguration) {
            $this->writeToConfiguration();
        }
        return $this;
    }

    /**
     * @param array $soapOptions
     * @param bool $writeToConfiguration
     * @return ClientFactory
     */
    public function setSoapOptions(array $soapOptions, bool $writeToConfiguration = true): ClientFactory
    {
        $this->soapOptions = $soapOptions;
        if ($writeToConfiguration) {
            $this->writeToConfiguration();
        }
        return $this;
    }

    /**
     * @param array $config
     * @param bool $writeToConfiguration
     * @return ClientFactory
     */
    public function setConfig(array $config, bool $writeToConfiguration = true): ClientFactory
    {
        $defaultConfig = [
            'security' => true,
            'timezone' => 'UTC',
        ];
        $config = array_merge($defaultConfig, $config);

        $this->config = $config;
        if ($writeToConfiguration) {
            $this->writeToConfiguration();
        }
        return $this;
    }

    /**
     * Write values to Configure
     */
    private function writeToConfiguration()
    {
        Configure::write('XMPieClient.xmp_options', $this->xmpOptions);
        Configure::write('XMPieClient.soap_options', $this->soapOptions);
        Configure::write('XMPieClient.config', $this->config);
    }

    /**
     *
     *
     * Clients for the Various XMPie WSDL Endpoints
     *
     *
     */

    /**
     * @param bool $refresh
     * @return AccountClient|null
     */
    public function AccountClient(bool $refresh = false): ?AccountClient
    {
        if ($refresh === true || $this->AccountClient === null) {
            $this->AccountClient = new AccountClient();
        }

        return $this->AccountClient;
    }

    /**
     * @param bool $refresh
     * @return CampaignClient|null
     */
    public function CampaignClient(bool $refresh = false): ?CampaignClient
    {
        if ($refresh === true || $this->CampaignClient === null) {
            $this->CampaignClient = new CampaignClient();
        }

        return $this->CampaignClient;
    }

    /**
     * @param bool $refresh
     * @return DataSourceClient|null
     */
    public function PlanClient(bool $refresh = false): ?PlanClient
    {
        if ($refresh === true || $this->PlanClient === null) {
            $this->PlanClient = new PlanClient();
        }

        return $this->PlanClient;
    }

    /**
     * @param bool $refresh
     * @return DataSourceClient|null
     */
    public function DataSourceClient(bool $refresh = false): ?DataSourceClient
    {
        if ($refresh === true || $this->DataSourceClient === null) {
            $this->DataSourceClient = new DataSourceClient();
        }

        return $this->DataSourceClient;
    }

    /**
     * @param bool $refresh
     * @return DocumentClient|null
     */
    public function DocumentClient(bool $refresh = false): ?DocumentClient
    {
        if ($refresh === true || $this->DocumentClient === null) {
            $this->DocumentClient = new DocumentClient();
        }

        return $this->DocumentClient;
    }

    /**
     * @param bool $refresh
     * @return TempStorageClient|null
     */
    public function TempStorageClient(bool $refresh = false): ?TempStorageClient
    {
        if ($refresh === true || $this->TempStorageClient === null) {
            $this->TempStorageClient = new TempStorageClient();
        }

        return $this->TempStorageClient;
    }


}