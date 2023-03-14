<?php

namespace App\XMPie\uProduce;

use SoapFault;
use XMPieWsdlClient\XMPie\uProduce\v_12_0_1\BasicServices\Plan_SSP\ArrayOfString;

class PlanClient extends BaseClient
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
        $Request = $this->RequestFabricator->Plan_SSP()
            ->IsExist()
            ->setInPlanID($id);
        $Service = $this->ServiceFabricator->Plan_SSP();
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
        $Request = $this->RequestFabricator->Plan_SSP()
            ->GetName()
            ->setInPlanID($id);
        $Service = $this->ServiceFabricator->Plan_SSP();
        $result = $Service->GetName($Request);

        return $result->getGetNameResult();
    }

    /**
     * @param $id
     * @return string[]|null
     * @throws SoapFault
     */
    public function getProperties($id): ?array
    {
        $strings = [
            'campaignID',
            'planName',
            'planCreated',
            'planModified',
            'createdBy',
            'modifiedBy',
            'checkOutUserID',
            'userCreateName',
            'userModifyName',
            'userCheckOutName',
        ];
        $p = new ArrayOfString();
        $p->setString($strings);

        $Request = $this->RequestFabricator->Plan_SSP()
            ->GetProperties()
            ->setInPlanID($id)
            ->setInPropertiesNames($p);
        $Service = $this->ServiceFabricator->Plan_SSP();
        $result = $Service->GetProperties($Request);

        $properties = [];
        foreach ($result->getGetPropertiesResult() as $prop) {
            $properties[$prop->getM_Name()] = $prop->getM_Value();
        }

        return $properties;
    }
}