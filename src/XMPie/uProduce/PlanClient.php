<?php

namespace App\XMPie\uProduce;

use SoapFault;

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
    public function getAllProperties($id): ?array
    {
        $Request = $this->RequestFabricator->Plan_SSP()
            ->GetAllProperties()
            ->setInPlanID($id);
        $Service = $this->ServiceFabricator->Plan_SSP();
        $result = $Service->GetAllProperties($Request);

        $properties = [];
        foreach ($result->getGetAllPropertiesResult() as $prop) {
            $properties[$prop->getM_Name()] = $prop->getM_Value();
        }

        return $properties;
    }
}