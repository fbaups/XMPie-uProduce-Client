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

    /**
     * @param $id
     * @return int|null
     * @throws SoapFault
     */
    public function getCampaignId($id): ?int
    {
        $Request = $this->RequestFabricator->Document_SSP()
            ->GetCampaign()
            ->setInDocumentID($id);
        $Service = $this->ServiceFabricator->Document_SSP();
        $result = $Service->GetCampaign($Request);

        return $result->getGetCampaignResult();
    }

    /**
     * The OUTPUT_RES Job Ticket Parameter can be supplied in the following format
     * - INT e.g. 72
     * - STRING e.g. fit-256 or fit-256-525 or fill-256 or fill-256-525
     *
     * this function looks at document width and height and calculates what resolution it
     * needs to render at to fit the desired output resolution
     *
     * @param int $id
     * @param int|string $dirtyResolution
     * @param int $fallbackResolution
     * @return int
     * @throws SoapFault
     */
    public function reformatOutputResolution(int $id, int|string $dirtyResolution, int $fallbackResolution = 72): int
    {
        $fallbackResolution = intval(round($fallbackResolution));

        if (is_numeric($dirtyResolution)) {
            return intval(round($dirtyResolution));
        }

        $outputResolution = explode("-", $dirtyResolution);
        $mode = strtolower($outputResolution[0]);
        if (!in_array($mode, ['fit', 'fill'])) {
            return $fallbackResolution;
        }

        if (count($outputResolution) < 2 || count($outputResolution) > 3) {
            return $fallbackResolution;
        }

        $width = $outputResolution[1];
        $height = $outputResolution[2] ?? $outputResolution[1];
        return $this->calculateRenderResolution($id, $width, $height, $mode);
    }

    /**
     * @param $id
     * @param $pixelsWide
     * @param $pixelsHigh
     * @param string $mode
     * @return int
     * @throws SoapFault
     */
    public function calculateRenderResolution($id, $pixelsWide, $pixelsHigh, string $mode = 'fit'): int
    {
        $docProperties = $this->getAllProperties($id);
        $docWidthPoints = $docProperties['PageWidth'];
        $docHeightPoints = $docProperties['PageHeight'];

        $pointsPerInch = 72;
        $docWidthInches = $docWidthPoints / $pointsPerInch;
        $docHeightInches = $docHeightPoints / $pointsPerInch;

        $widthResolution = $pixelsWide / $docWidthInches;
        $heightResolution = $pixelsHigh / $docHeightInches;
        $widthResolution = intval(round($widthResolution));
        $heightResolution = intval(round($heightResolution));

        if (strtolower($mode) === 'fit') {
            return min($widthResolution, $heightResolution);
        } elseif (strtolower($mode) === 'fill') {
            return max($widthResolution, $heightResolution);
        } else {
            return min($widthResolution, $heightResolution);
        }
    }
}