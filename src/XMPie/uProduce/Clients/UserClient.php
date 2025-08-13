<?php

namespace App\XMPie\uProduce\Clients;

use SoapFault;

class UserClient extends BaseClient
{
    public function __construct(array $xmpOptions = [], array $soapOptions = [], array $config = [])
    {
        parent::__construct($xmpOptions, $soapOptions, $config);
    }

    /**
     * @param $id
     * @return bool|null
     * @throws SoapFault
     */
    public function isExist($id): ?bool
    {
        $Request = $this->RequestFabricator->User_SSP()
            ->IsExist()
            ->setInUserID($id);
        $Service = $this->ServiceFabricator->User_SSP();
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
        //native method does not exist so go via getAllProperties()
        $props = $this->getAllProperties($id);

        if (isset($props['LoginName'])) {
            return $props['LoginName'];
        } elseif (isset($props['userEmail'])) {
            return $props['userEmail'];
        } elseif (isset($props['userFirstName']) && isset($props['userLastName'])) {
            return $props['userFirstName'] . " " . $props['userLastName'];
        } else {
            return '';
        }
    }

    /**
     * @param $name
     * @return string|null
     * @throws SoapFault
     */
    public function getId($name): ?string
    {
        $Request = $this->RequestFabricator->User_SSP()
            ->GetID()
            ->setInUserName($name);
        $Service = $this->ServiceFabricator->User_SSP();
        $result = $Service->GetID($Request);

        return $result->getGetIDResult();
    }

    /**
     * @param $id
     * @return string[]|null
     * @throws SoapFault
     */
    public function getAllProperties($id): ?array
    {

        $Request = $this->RequestFabricator->User_SSP()
            ->GetAllProperties()
            ->setInUserID($id);
        $Service = $this->ServiceFabricator->User_SSP();
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
        $Request = $this->RequestFabricator->User_SSP()
            ->Delete()
            ->setInUserID($id);
        $Service = $this->ServiceFabricator->User_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }
}