<?php

namespace App\XMPie\uProduce;

use SoapFault;
use XMPieWsdlClient\XMPie\uProduce\v_12_0_1\BasicServices\Account_SSP\ArrayOfString;

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
     * @return string|null
     * @throws SoapFault
     */
    public function getId($name): ?string
    {
        $Request = $this->RequestFabricator->Account_SSP()
            ->GetID()
            ->setInAccountName($name);
        $Service = $this->ServiceFabricator->Account_SSP();
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
            'accountName',
            'accountDescription',
            'accountCreated',
            'accountModified',
            'createdBy',
            'modifiedBy',
            'customerID',
            'userCreateName',
            'userModifyName',
        ];
        $p = new ArrayOfString();
        $p->setString($strings);

        $Request = $this->RequestFabricator->Account_SSP()
            ->GetProperties()
            ->setInAccountID($id)
            ->setInPropertiesNames($p);
        $Service = $this->ServiceFabricator->Account_SSP();
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
        $Request = $this->RequestFabricator->Account_SSP()
            ->Delete()
            ->setInAccountID($id);
        $Service = $this->ServiceFabricator->Account_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }
}