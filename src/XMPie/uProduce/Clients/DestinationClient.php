<?php

namespace App\XMPie\uProduce\Clients;

use SoapFault;

class DestinationClient extends BaseClient
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
        $Request = $this->RequestFabricator->Destination_SSP()
            ->IsExist()
            ->setInDestinationID($id);
        $Service = $this->ServiceFabricator->Destination_SSP();
        $result = $Service->IsExist($Request);

        return $result->getIsExistResult();
    }

    /**
     * Validate the Destination by Name or ID.
     * Will check that the current username/password can actually access the Destination
     * Will return the Destination ID or false
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
                    if (isset($props['printerID'])) {
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
        //native method does not exist so go via getAllProperties()
        $props = $this->getAllProperties($id);

        if (isset($props['printerName'])) {
            return $props['printerName'];
        } else {
            return '';
        }
    }

    /**
     * @param $name
     * @return int|null
     * @throws SoapFault
     */
    public function getId($name): ?int
    {
        $Request = $this->RequestFabricator->Destination_SSP()
            ->GetID()
            ->setInDestinationName($name);
        $Service = $this->ServiceFabricator->Destination_SSP();
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
        $Request = $this->RequestFabricator->Destination_SSP()
            ->GetAllProperties()
            ->setInDestinationID($id);
        $Service = $this->ServiceFabricator->Destination_SSP();
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
        $Request = $this->RequestFabricator->Destination_SSP()
            ->Delete()
            ->setInDestinationID($id);
        $Service = $this->ServiceFabricator->Destination_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }
}