<?php

namespace App\XMPie\uProduce;

use SoapFault;
use XMPieWsdlClient\XMPie\uProduce\v_12_0_1\BasicServices\Campaign_SSP\ArrayOfString;

class CampaignClient extends BaseClient
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $id
     * @return string|null
     * @throws SoapFault
     */
    public function isExist($id): ?string
    {
        $Request = $this->RequestFabricator->Campaign_SSP()
            ->IsExist()
            ->setInCampaignID($id);
        $Service = $this->ServiceFabricator->Campaign_SSP();
        $result = $Service->IsExist($Request);

        return $result->getIsExistResult();
    }

    /**
     * @param $id
     * @return string|null
     * @throws SoapFault
     */
    public function getName($id): ?string
    {
        $Request = $this->RequestFabricator->Campaign_SSP()
            ->GetName()
            ->setInCampaignID($id);
        $Service = $this->ServiceFabricator->Campaign_SSP();
        $result = $Service->GetName($Request);

        return $result->getGetNameResult();
    }

    /**
     * @param $name
     * @param $accountId
     * @return string|null
     * @throws SoapFault
     */
    public function getId($name, $accountId): ?string
    {
        $Request = $this->RequestFabricator->Campaign_SSP()
            ->GetID()
            ->setInCampaignName($name)
            ->setInAccountID($accountId);
        $Service = $this->ServiceFabricator->Campaign_SSP();
        $result = $Service->GetID($Request);

        return $result->getGetIDResult();
    }

    /**
     * @param $id
     * @return string[]|null
     * @throws SoapFault
     */
    public function getProperties($id): ?array
    {
        $strings = [
            'accountID',
            'campaignName',
            'campaignDescription',
            'campaignCreated',
            'campaignModified',
            'createdBy',
            'modifiedBy',
            'currentPlanID',
            'currentDataSourceIDs',
            'userCreateName',
            'userModifyName',
            'userFirstName',
            'userLastName',
            'userEmail',
            'userPhoneNumber',
            'customerName',
        ];
        $p = new ArrayOfString();
        $p->setString($strings);

        $Request = $this->RequestFabricator->Campaign_SSP()
            ->GetProperties()
            ->setInCampaignID($id)
            ->setInPropertiesNames($p);
        $Service = $this->ServiceFabricator->Campaign_SSP();
        $result = $Service->GetProperties($Request);

        $properties = [];
        foreach ($result->getGetPropertiesResult() as $prop) {
            $properties[$prop->getM_Name()] = $prop->getM_Value();
        }

        return $properties;
    }

    /**
     * @param $id
     * @return bool|null
     * @throws SoapFault
     */
    public function delete($id): ?bool
    {
        $Request = $this->RequestFabricator->Campaign_SSP()
            ->Delete()
            ->setInCampaignID($id);
        $Service = $this->ServiceFabricator->Campaign_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }
}