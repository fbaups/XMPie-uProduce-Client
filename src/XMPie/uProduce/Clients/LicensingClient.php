<?php

namespace App\XMPie\uProduce\Clients;

use Cake\Core\Configure;
use Cake\Utility\Xml;
use SoapFault;

class LicensingClient extends BaseClient
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string|null
     * @throws SoapFault
     */
    public function getServerId(): ?string
    {
        $Request = $this->RequestFabricator->Licensing_SSP()
            ->GetServerID();
        $Service = $this->ServiceFabricator->Licensing_SSP();
        $result = $Service->GetServerID($Request);

        return $result->getGetServerIDResult();
    }

    /**
     * @return float|null
     * @throws SoapFault
     */
    public function getAvailableClicks(): ?float
    {
        $Request = $this->RequestFabricator->Licensing_SSP()
            ->GetAvailableClicks();
        $Service = $this->ServiceFabricator->Licensing_SSP();
        $result = $Service->GetAvailableClicks($Request);

        return floatval($result->getGetAvailableClicksResult());
    }

    /**
     * @return bool|null
     * @throws SoapFault
     */
    public function isPerpetual(): ?bool
    {
        $adminUsername = Configure::read('XMPieClient.xmp_options.admin_username');
        $adminPassword = Configure::read('XMPieClient.xmp_options.admin_password');
        $Request = $this->RequestFabricator->Licensing_SSP()
            ->IsPerpetual()
            ->setInUsername($adminUsername)
            ->setInPassword($adminPassword);
        $Service = $this->ServiceFabricator->Licensing_SSP();
        $result = $Service->IsPerpetual($Request);

        return $result->getIsPerpetualResult();
    }

    /**
     * @return bool|null
     * @throws SoapFault
     */
    public function isMIAvailable(): ?bool
    {
        $adminUsername = Configure::read('XMPieClient.xmp_options.admin_username');
        $adminPassword = Configure::read('XMPieClient.xmp_options.admin_password');
        $Request = $this->RequestFabricator->Licensing_SSP()
            ->IsMIAvailable()
            ->setInUsername($adminUsername)
            ->setInPassword($adminPassword);
        $Service = $this->ServiceFabricator->Licensing_SSP();
        $result = $Service->IsMIAvailable($Request);

        return $result->getIsMIAvailableResult();
    }

    /**
     * @return array
     * @throws SoapFault
     */
    public function getConnectivityLicenses(): array
    {
        $adminUsername = Configure::read('XMPieClient.xmp_options.admin_username');
        $adminPassword = Configure::read('XMPieClient.xmp_options.admin_password');
        $Request = $this->RequestFabricator->Licensing_SSP()
            ->GetConnectivityLicenses()
            ->setInUsername($adminUsername)
            ->setInPassword($adminPassword);
        $Service = $this->ServiceFabricator->Licensing_SSP();
        $result = $Service->GetConnectivityLicenses($Request);

        $xmlString = $result->getGetConnectivityLicensesResult()->getAny();
        $xml = Xml::build($xmlString);
        return Xml::toArray($xml);
    }

    /**
     * @return array
     * @throws SoapFault
     */
    public function getFeatureClients(int $featureTypeId): mixed
    {
        $adminUsername = Configure::read('XMPieClient.xmp_options.admin_username');
        $adminPassword = Configure::read('XMPieClient.xmp_options.admin_password');
        $Request = $this->RequestFabricator->Licensing_SSP()
            ->GetFeatureClients()
            ->setInFeatureTypeID($featureTypeId)
            ->setInUsername($adminUsername)
            ->setInPassword($adminPassword);
        $Service = $this->ServiceFabricator->Licensing_SSP();
        $result = $Service->GetFeatureClients($Request);

        $xmlString = $result->getGetFeatureClientsResult()->getAny();
        $xml = Xml::build($xmlString);
        return Xml::toArray($xml);
    }

    /**
     * @return array
     * @throws SoapFault
     */
    public function getMaximumFeatureClients(int $featureTypeId): mixed
    {
        $adminUsername = Configure::read('XMPieClient.xmp_options.admin_username');
        $adminPassword = Configure::read('XMPieClient.xmp_options.admin_password');
        $Request = $this->RequestFabricator->Licensing_SSP()
            ->GetMaximumFeatureClients()
            ->setInFeatureTypeID($featureTypeId)
            ->setInUsername($adminUsername)
            ->setInPassword($adminPassword);
        $Service = $this->ServiceFabricator->Licensing_SSP();
        $result = $Service->GetMaximumFeatureClients($Request);

        return $result->getGetMaximumFeatureClientsResult();
    }

    /**
     * @return array
     * @throws SoapFault
     */
    public function disconnectConnectivityLicenses(): array
    {
        $adminUsername = Configure::read('XMPieClient.xmp_options.admin_username');
        $adminPassword = Configure::read('XMPieClient.xmp_options.admin_password');

        $a = $this->getConnectivityLicenses();

        if (isset($a['diffgram']['NewDataSet']['Table'][0])) {
            $tables = $a['diffgram']['NewDataSet']['Table'];
        } else {
            if (isset($a['diffgram']['NewDataSet']['Table'])) {
                $tables = [$a['diffgram']['NewDataSet']['Table']];
            } else {
                $tables = [];
            }
        }

        $results = [];
        foreach ($tables as $table) {
            $Request = $this->RequestFabricator->Licensing_SSP()
                ->DeleteConnectivityLicense()
                ->setInUsername($adminUsername)
                ->setInPassword($adminPassword)
                ->setInConnectivityLicenseID($table['clientID']);
            $Service = $this->ServiceFabricator->Licensing_SSP();
            $Response = $Service->DeleteConnectivityLicense($Request);
            $results[] = $Response->getDeleteConnectivityLicenseResult();
        }

        return $results;
    }
}