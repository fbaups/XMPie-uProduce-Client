<?php

namespace App\XMPie\uProduce\Clients;

use Cake\Core\Configure;

class ClientFactory
{
    private array $xmpOptions;
    private array $soapOptions;
    private array $config;

    private ?DestinationClient $DestinationClient = null;
    private ?UserClient $UserClient = null;
    private ?CustomerClient $CustomerClient = null;
    private ?AccountClient $AccountClient = null;
    private ?CampaignClient $CampaignClient = null;
    private ?PlanClient $PlanClient = null;
    private ?DataSourceClient $DataSourceClient = null;
    private ?DocumentClient $DocumentClient = null;
    private ?TempStorageClient $TempStorageClient = null;
    private ?LicensingClient $LicensingClient = null;
    private ?JobClient $JobClient = null;
    private ?JobMessageClient $JobMessageClient = null;

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
     * @return CustomerClient|null
     */
    public function CustomerClient(bool $refresh = false): ?CustomerClient
    {
        if ($refresh === true || $this->CustomerClient === null) {
            $this->CustomerClient = new CustomerClient();
        }

        return $this->CustomerClient;
    }

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
     * @return PlanClient|null
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

    /**
     * @param bool $refresh
     * @return DestinationClient|null
     */
    public function DestinationClient(bool $refresh = false): ?DestinationClient
    {
        if ($refresh === true || $this->DestinationClient === null) {
            $this->DestinationClient = new DestinationClient();
        }

        return $this->DestinationClient;
    }

    /**
     * @param bool $refresh
     * @return UserClient|null
     */
    public function UserClient(bool $refresh = false): ?UserClient
    {
        if ($refresh === true || $this->UserClient === null) {
            $this->UserClient = new UserClient();
        }

        return $this->UserClient;
    }

    /**
     * @param bool $refresh
     * @return LicensingClient|null
     */
    public function LicensingClient(bool $refresh = false): ?LicensingClient
    {
        if ($refresh === true || $this->LicensingClient === null) {
            $this->LicensingClient = new LicensingClient();
        }

        return $this->LicensingClient;
    }

    /**
     * @param bool $refresh
     * @return JobClient|null
     */
    public function JobClient(bool $refresh = false): ?JobClient
    {
        if ($refresh === true || $this->JobClient === null) {
            $this->JobClient = new JobClient();
        }

        return $this->JobClient;
    }

    /**
     * @param bool $refresh
     * @return JobMessageClient|null
     */
    public function JobMessageClient(bool $refresh = false): ?JobMessageClient
    {
        if ($refresh === true || $this->JobMessageClient === null) {
            $this->JobMessageClient = new JobMessageClient();
        }

        return $this->JobMessageClient;
    }


}