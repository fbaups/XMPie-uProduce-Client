<?php

namespace App\XMPie\uProduce\Clients;

use SoapFault;

class AccountClient extends BaseClient
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $id
     * @return bool|null
     * @throws SoapFault
     */
    public function isExist($id): ?bool
    {
        $Request = $this->RequestFabricator->Account_SSP()
            ->IsExist()
            ->setInAccountID($id);
        $Service = $this->ServiceFabricator->Account_SSP();
        $result = $Service->IsExist($Request);

        return $result->getIsExistResult();
    }

    /**
     * Validate the Account by Name or ID.
     * Will check that the current username/password can actually access the Account
     * Will return the Account ID or false
     *
     * @param int|string $nameOrId
     * @return int|false
     * @throws SoapFault
     */
    public function validate(int|string $nameOrId): bool|int
    {
        if (is_numeric($nameOrId)) {
            if ($this->isExist($nameOrId)) {
                try {
                    $props = $this->getAllProperties($nameOrId);
                    if (isset($props['accountID'])) {
                        return intval($nameOrId);
                    } else {
                        return false;
                    }
                } catch (\Throwable $exception) {
                    return false;
                }
            } else {
                return false;
            }
        } elseif (is_string($nameOrId)) {
            $id = $this->getId($nameOrId);
            if ($id !== 0) {
                return $id;
            } else {
                return false;
            }
        }

        return false;
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