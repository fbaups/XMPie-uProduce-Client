<?php

namespace App\XMPie\uProduce\Clients;

use SoapFault;

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
     * Validate the Campaign by Name or ID.
     * Will check that the current username/password can actually access the Campaign
     * Will return the Campaign ID or false
     *
     * @param int|string $nameOrId
     * @param int|null $accountId only needed if you are trying to validate a name as names are not unique across Accounts (i.e. you need to target specific Account)
     * @return int|false
     * @throws SoapFault
     */
    public function validate(int|string $nameOrId, int $accountId = null): bool|int
    {
        if (is_numeric($nameOrId)) {
            if ($this->isExist($nameOrId)) {
                try {
                    $props = $this->getAllProperties($nameOrId);
                    if (isset($props['campaignID'])) {
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
            if (is_int($accountId)) {
                $id = $this->getId($nameOrId, $accountId);
                if ($id !== 0) {
                    return $id;
                } else {
                    return false;
                }
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
     * @return int|null
     * @throws SoapFault
     */
    public function getId($name, $accountId): ?int
    {
        $Request = $this->RequestFabricator->Campaign_SSP()
            ->GetID()
            ->setInCampaignName($name)
            ->setInAccountID($accountId);
        $Service = $this->ServiceFabricator->Campaign_SSP();
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
        $Request = $this->RequestFabricator->Campaign_SSP()
            ->GetAllProperties()
            ->setInCampaignID($id);
        $Service = $this->ServiceFabricator->Campaign_SSP();
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
        $Request = $this->RequestFabricator->Campaign_SSP()
            ->Delete()
            ->setInCampaignID($id);
        $Service = $this->ServiceFabricator->Campaign_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }
}