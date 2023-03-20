<?php

namespace App\XMPie\uProduce\Clients;

use SoapFault;

class DocumentClient extends BaseClient
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
        $Request = $this->RequestFabricator->Document_SSP()
            ->IsExist()
            ->setInDocumentID($id);
        $Service = $this->ServiceFabricator->Document_SSP();
        $result = $Service->IsExist($Request);

        return $result->getIsExistResult();
    }

    /**
     * Validate the Document by ID.
     * Will check that the current username/password can actually access the Document
     * Will return the Document ID or false
     *
     * You cannot validate a Document Name as Document Names are not unique
     *
     * @param int $id
     * @return int|false
     * @throws SoapFault
     */
    public function validate(int $id): bool|int
    {
        if ($this->isExist($id)) {
            try {
                $props = $this->getAllProperties($id);
                if (isset($props['planID'])) {
                    return intval($id);
                } else {
                    return false;
                }
            } catch (\Throwable $exception) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $id
     * @return string|null
     * @throws SoapFault
     */
    public function getName($id): ?string
    {
        $Request = $this->RequestFabricator->Document_SSP()
            ->GetName()
            ->setInDocumentID($id);
        $Service = $this->ServiceFabricator->Document_SSP();
        $result = $Service->GetName($Request);

        return $result->getGetNameResult();
    }

    /**
     * @param $name
     * @param $campaignId
     * @return int|null
     * @throws SoapFault
     */
    public function getId($name, $campaignId): ?int
    {
        $Request = $this->RequestFabricator->Document_SSP()
            ->GetID()
            ->setInDocumentName($name)
            ->setInCampaignID($campaignId);
        $Service = $this->ServiceFabricator->Document_SSP();
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
        $Request = $this->RequestFabricator->Document_SSP()
            ->GetAllProperties()
            ->setInDocumentID($id);
        $Service = $this->ServiceFabricator->Document_SSP();
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
        $Request = $this->RequestFabricator->Document_SSP()
            ->Delete()
            ->setInDocumentID($id);
        $Service = $this->ServiceFabricator->Document_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }
}