<?php

namespace App\XMPie\uProduce\Clients;

use Cake\Core\Configure;
use SoapFault;

class JobMessageClient extends BaseClient
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $id
     * @return string[]|null
     * @throws SoapFault
     */
    public function getAllProperties($id): ?array
    {
        $adminUsername = Configure::read('XMPieClient.xmp_options.admin_username');
        $adminPassword = Configure::read('XMPieClient.xmp_options.admin_password');

        $Request = $this->RequestFabricator->JobMessage_SSP()
            ->GetAllProperties()
            ->setInUsername($adminUsername)
            ->setInPassword($adminPassword)
            ->setInMessageID($id);
        $Service = $this->ServiceFabricator->JobMessage_SSP();
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
        $Request = $this->RequestFabricator->JobMessage_SSP()
            ->Delete()
            ->setInMessageID($id);
        $Service = $this->ServiceFabricator->JobMessage_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }
}