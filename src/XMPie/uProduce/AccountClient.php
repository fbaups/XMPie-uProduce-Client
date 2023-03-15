<?php

namespace App\XMPie\uProduce;

use SoapFault;

class AccountClient extends BaseClient
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
        $Request = $this->RequestFabricator->Account_SSP()
            ->IsExist()
            ->setInAccountID($id);
        $Service = $this->ServiceFabricator->Account_SSP();
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
        $Request = $this->RequestFabricator->Account_SSP()
            ->GetName()
            ->setInAccountID($id);
        $Service = $this->ServiceFabricator->Account_SSP();
        $result = $Service->GetName($Request);

        return $result->getGetNameResult();
    }

    /**
     * @param $name
     * @return int|null
     * @throws SoapFault
     */
    public function getId($name): ?int
    {
        $Request = $this->RequestFabricator->Account_SSP()
            ->GetID()
            ->setInAccountName($name);
        $Service = $this->ServiceFabricator->Account_SSP();
        $result = $Service->GetID($Request);

        return intval($result->getGetIDResult());
    }

    /**
     * @param $id
     * @return string[]|null
     * @throws SoapFault
     */
    public function getAllProperties($id): ?array
    {
        $Request = $this->RequestFabricator->Account_SSP()
            ->GetAllProperties()
            ->setInAccountID($id);
        $Service = $this->ServiceFabricator->Account_SSP();
        $result = $Service->GetAllProperties($Request);

        $properties = [];
        foreach ($result->getGetAllPropertiesResult() as $prop) {
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
        $Request = $this->RequestFabricator->Account_SSP()
            ->Delete()
            ->setInAccountID($id);
        $Service = $this->ServiceFabricator->Account_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }
}