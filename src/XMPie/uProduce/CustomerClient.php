<?php

namespace App\XMPie\uProduce;

use SoapFault;

class CustomerClient extends BaseClient
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string|null
     * @throws SoapFault
     */
    public function isExist(): ?string
    {
        //there is always only one customer in uProduce and it must exist
        return true;
    }

    /**
     * @return string|null
     * @throws SoapFault
     */
    public function getName(): ?string
    {
        //native method does not exist so go via getAllProperties()
        $props = $this->getAllProperties();

        if (isset($props['customerName'])) {
            return $props['customerName'];
        } else {
            return '';
        }
    }

    /**
     * @return int|null
     * @throws SoapFault
     */
    public function getId(): ?int
    {
        //native method does not exist so go via getAllProperties()
        $props = $this->getAllProperties();

        if (isset($props['customerID'])) {
            return intval($props['customerID']);
        } else {
            return 0;
        }
    }

    /**
     * @param $id
     * @return string[]|null
     * @throws SoapFault
     */
    public function getAllProperties(): ?array
    {
        $Request = $this->RequestFabricator->Customer_SSP()
            ->GetAllProperties();
        $Service = $this->ServiceFabricator->Customer_SSP();
        $result = $Service->GetAllProperties($Request);

        $properties = [];
        foreach ($result->getGetAllPropertiesResult() as $prop) {
            $properties[$prop->getM_Name()] = $prop->getM_Value();
        }

        return $properties;
    }

    /**
     * @return string[]|null
     * @throws SoapFault
     */
    public function getAccounts(): ?array
    {
        $Request = $this->RequestFabricator->Customer_SSP()
            ->GetAccounts();
        $Service = $this->ServiceFabricator->Customer_SSP();
        $result = $Service->GetAccounts($Request);

        $AC = new AccountClient();

        $accounts = [];
        foreach ($result->getGetAccountsResult() as $accountId) {
            $accountId = intval($accountId);
            $accounts[$accountId] = $AC->getAllProperties($accountId);
        }

        return $accounts;
    }

    /**
     * @return string[]|null
     * @throws SoapFault
     */
    public function getUsers(): ?array
    {
        $Request = $this->RequestFabricator->Customer_SSP()
            ->GetUsers();
        $Service = $this->ServiceFabricator->Customer_SSP();
        $result = $Service->GetUsers($Request);

        $UC = new UserClient();

        $users = [];
        foreach ($result->getGetUsersResult() as $userId) {
            $userId = intval($userId);
            $users[$userId] = $UC->getAllProperties($userId);
        }

        return $users;
    }

    /**
     * @param int|null $type 1=FTP Site 2=Network Path 3=Network Printer 4=Xerox FreeFlow Print Manager (FFPM)
     * @return string[]|null
     * @throws SoapFault
     */
    public function getDestinations(int $type = null): ?array
    {
        $Request = $this->RequestFabricator->Customer_SSP()
            ->GetDestinations();

        if($type){
            $Request = $Request->setInType($type);
        }

        $Service = $this->ServiceFabricator->Customer_SSP();
        $result = $Service->GetDestinations($Request);

        $UC = new DestinationClient();

        $destinations = [];
        foreach ($result->getGetDestinationsResult() as $userId) {
            $userId = intval($userId);
            $destinations[$userId] = $UC->getAllProperties($userId);
        }

        return $destinations;
    }
}