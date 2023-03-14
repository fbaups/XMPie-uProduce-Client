<?php

namespace App\XMPie\uProduce;

use SoapFault;
use XMPieWsdlClient\XMPie\uProduce\v_12_0_1\BasicServices\Document_SSP\ArrayOfString;

class DocumentClient extends BaseClient
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
        $Request = $this->RequestFabricator->Document_SSP()
            ->IsExist()
            ->setInDocumentID($id);
        $Service = $this->ServiceFabricator->Document_SSP();
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
     * @return string|null
     * @throws SoapFault
     */
    public function getId($name, $campaignId): ?string
    {
        $Request = $this->RequestFabricator->Document_SSP()
            ->GetID()
            ->setInDocumentName($name)
            ->setInCampaignID($campaignId);
        $Service = $this->ServiceFabricator->Document_SSP();
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
            'campaignID',
            'documentName',
            //'documentLogicalName',
            'documentCreated',
            'documentModified',
            'createdBy',
            'modifiedBy',
            'documentStatus',
            'documentType',
            'thumbnail',
            'creatorApplication',
            'plugInVersion',
            'numberOfPages',
            'pageWidth',
            'pageHeight',
            'preferredPrinterID',
            'checkOutUserID',
            'planID',
            'planName',
            'campaignModified',
            'userCreateName',
            'userModifyName',
            'userCheckOutName',
        ];
        $p = new ArrayOfString();
        $p->setString($strings);

        $Request = $this->RequestFabricator->Document_SSP()
            ->GetProperties()
            ->setInDocumentID($id)
            ->setInPropertiesNames($p);
        $Service = $this->ServiceFabricator->Document_SSP();
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
        $Request = $this->RequestFabricator->Document_SSP()
            ->Delete()
            ->setInDocumentID($id);
        $Service = $this->ServiceFabricator->Document_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }
}