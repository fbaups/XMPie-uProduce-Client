<?php

namespace App\XMPie\uProduce\Clients;

use arajcany\PrePressTricks\Utilities\PDFGeometry;
use SoapFault;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\ProductionServices\JobTicket_SSP\ArrayOfParameter;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\ProductionServices\JobTicket_SSP\ArrayOfString;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\ProductionServices\JobTicket_SSP\Connection;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\ProductionServices\JobTicket_SSP\Customization;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\ProductionServices\JobTicket_SSP\parameter;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\ProductionServices\JobTicket_SSP\RecipientsInfo;

class JobTicketClient extends BaseClient
{
    public function __construct(array $xmpOptions = [], array $soapOptions = [], array $config = [])
    {
        parent::__construct($xmpOptions, $soapOptions, $config);
    }

    /**
     * @param $id
     * @return int|null
     * @throws SoapFault
     */
    public function createNewTicket(): ?int
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->CreateNewTicket();
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->CreateNewTicket($Request);

        return intval($result->getCreateNewTicketResult());
    }

    /**
     * @param $ticketId
     * @param bool $pretty
     * @return string|null
     * @throws SoapFault
     */
    public function getTicket($ticketId, bool $pretty = false): ?string
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->GetTicket()
            ->setInTicketID($ticketId);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->GetTicket($Request);

        $xmlString = $result->getGetTicketResult();

        if ($pretty) {
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = true;
            $dom->formatOutput = true;
            $dom->loadXML($xmlString);
            $xmlString = $dom->saveXML();
        }

        return $xmlString;
    }

    /**
     * @param $ticketId
     * @param int $campaignId
     * @return bool|null
     * @throws SoapFault
     */
    public function setCampaignID($ticketId, int $campaignId): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetCampaignID()
            ->setInTicketID($ticketId)
            ->setInCampaignID($campaignId);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetCampaignID($Request);

        return $result->getSetCampaignIDResult();
    }

    /**
     * @param $ticketId
     * @param int $documentID
     * @return bool|null
     * @throws SoapFault
     */
    public function setDocumentID($ticketId, int $documentID): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetDocumentByID()
            ->setInTicketID($ticketId)
            ->setInDocumentID($documentID);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetDocumentByID($Request);

        return $result->getSetDocumentByIDResult();
    }

    /**
     * @param $ticketId
     * @param int $planID
     * @return bool|null
     * @throws SoapFault
     */
    public function setPlanID($ticketId, int $planID): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetPlanByID()
            ->setInTicketID($ticketId)
            ->setInPlanID($planID)
            ->setInUseTrivial(false);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetPlanByID($Request);

        return $result->getSetPlanByIDResult();
    }

    /**
     * @param $ticketId
     * @param int $dataSourceID
     * @return bool|null
     * @throws SoapFault
     */
    public function setDataSourceID($ticketId, int $dataSourceID): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetDataSourceByID()
            ->setInTicketID($ticketId)
            ->setInDataSourceID($dataSourceID)
            ->setInSchemaName('MySchema');
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetDataSourceByID($Request);

        return $result->getSetDataSourceByIDResult();
    }

    /**
     * @param $ticketId
     * @return bool|null
     * @throws SoapFault
     */
    public function removeAllRIs($ticketId): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->RemoveAllRIs()
            ->setInTicketID($ticketId);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->RemoveAllRIs($Request);

        return $result->getRemoveAllRIsResult();
    }

    /**
     * @param $ticketId
     * @param int $from
     * @param int $to
     * @return bool|null
     * @throws SoapFault
     */
    public function setRIRange($ticketId, int $from, int $to): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetRIRange()
            ->setInTicketID($ticketId)
            ->setInRangeFrom($from)
            ->setInRangeTo($to);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetRIRange($Request);

        return $result->getSetRIRangeResult();
    }

    /**
     * Looks at all the options and figure out how to create a RI
     *
     * @param $ticketId
     * @param $options
     * @return bool
     * @throws SoapFault
     */
    public function setRIs($ticketId, $options): bool
    {
        /*
         * Order of precedence:
         *  - If a DataSource ID has been provided use the DataSource
         *  - If DataSource ID not present assume that VARIABLE/ADOR customisations will be used
         *
         * When using a DataSource ID:
         *  - Use Query if present
         *  - Fallback to Plan Filter Name if present
         *  - Fallback to Table Name if present
         *  - Fallback to selecting the first compatible table name
         *  - DataSource is incompatiblee so assume that VARIABLE/ADOR customisations will be used
         */

        /*
         * ====m_Filter====
         * The name of the Recipient filter, to be specified as follows:
         * Type                 Filter Name
         * Recipient ID         The name of the Data Source table containing the record of the recipient indicated by the specified ID. This ID is provided using the SetRIOnDemandInfo method.
         * Query                The user-defined SQL query to be used to return the Recipient List. For example: "SELECT * FROM MyTableName".
         * Plan                 Filter Name The name of the filter as it is defined in the Plan file.
         * Table name           The name of the Data Source table to be used in its entirety as the Recipient List.
         * No Recipient List    An empty string.
         *
         * ====m_FilterType====
         * The type of Recipient filter. Specify one of the following values:
         * 0 RecipientID
         * 1 Query
         * 2 Plan Filter Name
         * 3 Table name
         * 4 No Recipient List.Note NoteThis is relevant when all ADOR Objects are customized with fixed values of a single recipient (for example, when creating a business card), so there is no need to extract a Recipient List from a Data Source.
         */

        $defaultOptions = [
            'dataSourceId' => null,
            'query' => null,
            'planFilterName' => null,
            'tableName' => null,
            'startRecord' => null,
            'endRecord' => null,
        ];

        $options = array_merge($defaultOptions, $options);

        if ($options['dataSourceId']) {
            $query = $options['query'];
            $planFilterName = $options['planFilterName'];
            $tableName = $this->getValidTableNameForDataSource($options['dataSourceId'], $options['tableName']);;

            if ($query) {
                $filter = $query;
                $filterType = 1;
            } elseif ($planFilterName) {
                $filter = $planFilterName;
                $filterType = 2;
            } elseif ($tableName) {
                $filter = $tableName;
                $filterType = 3;
            } else {
                $filter = '';
                $filterType = 4;
            }
        } else {
            $filter = '';
            $filterType = 4;
        }

        $RI = $this->createRecipientsInfo($filter, $filterType);

        if (in_array($filterType, [1, 2, 3])) {
            $this->setDataSourceID($ticketId, $options['dataSourceId']);
            $this->removeAllRIs($ticketId);
            $this->setRIRange($ticketId, $options['startRecord'], $options['endRecord']);
            $this->addRIByID($ticketId, $options['dataSourceId'], $RI);
            $this->removeAllSchemaDataSources($ticketId);
        } elseif (in_array($filterType, [4])) {
            $this->addRI($ticketId, $RI);
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param $ticketId
     * @return int|null
     * @throws SoapFault
     */
    public function getRIFrom($ticketId): ?int
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->GetRIFrom()
            ->setInTicketID($ticketId);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->GetRIFrom($Request);

        return intval($result->getGetRIFromResult());
    }

    /**
     * @param $ticketId
     * @return int|null
     * @throws SoapFault
     */
    public function getRITo($ticketId): ?int
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->GetRITo()
            ->setInTicketID($ticketId);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->GetRITo($Request);

        return intval($result->getGetRIToResult());
    }


    /**
     * @param string $filter
     * @param int $filterType
     * @param string $subFilter
     * @param string $recipientIDListFileName
     * @param string $recipientIDListMergeType
     * @param bool $sortByPrimaryField
     * @return RecipientsInfo
     */
    public function createRecipientsInfo(string $filter, int $filterType, string $subFilter = '', string $recipientIDListFileName = '', string $recipientIDListMergeType = '', bool $sortByPrimaryField = true): RecipientsInfo
    {
        /**
         * $filter
         * The name of the Recipient filter, to be specified as follows:
         * Type                 Filter Name
         * Recipient ID         The name of the Data Source table containing the record of the recipient indicated
         *                          by the specified ID. This ID is provided using the SetRIOnDemandInfo method.
         * Query                The user-defined SQL query to be used to return the Recipient List.
         *                          For example: "SELECT * FROM MyTableName".
         * Plan                 Filter Name The name of the filter as it is defined in the Plan file.
         * Table name           The name of the Data Source table to be used in its entirety as the Recipient List.
         * No Recipient List    An empty string.
         *
         * $filterType
         * The type of Recipient filter. Specify one of the following values:
         * 0 RecipientID
         * 1 Query
         * 2 Plan Filter Name
         * 3 Table name
         * 4 No Recipient List.Note NoteThis is relevant when all ADOR Objects are customized with fixed
         *          values of a single recipient (for example, when creating a business card),
         *          so there is no need to extract a Recipient List from a Data Source.
         *
         * $subFilter
         * An additional filter that can be used in conjunction with the m_Filter option.
         * Possible options are "XMPieCreationDate", "XMPieModificationDate", "XMPieIsImported" and "XMPieModificationCounter".
         *
         * $recipientIDListFileName
         * A file containing a list of Recipient IDs.
         * This parameter is optional and will be ignored if not specified.
         * The plan interpreter will process only recipients that appear both in this file
         * and in the m_filter + m_SubFilter definition.
         */

        $ri = (new RecipientsInfo())
            ->setM_From(-1)//static value leave as -1
            ->setM_To(-1)//static value leave as -1
            ->setM_Filter($filter)//name of the table in the SQL Database
            ->setM_FilterType($filterType)
            ->setM_SubFilter($subFilter)
            ->setM_recipientIDListFileName($recipientIDListFileName)
            ->setM_recipientIDListMergeType($recipientIDListMergeType)
            ->setM_SortByPrimaryField($sortByPrimaryField);

        return $ri;
    }

    /**
     * Forcibly get a valid table name for then Plan & DataSourceID combination.
     * Will validate the given table name (if given) or use the first compatible table.
     * On failure will return false
     *
     * @param int $dataSourceId
     * @param string|null $dataSourceTableName
     * @return string|false
     * @throws SoapFault
     */
    public function getValidTableNameForDataSource(int $dataSourceId, string $dataSourceTableName = null): bool|string
    {
        $DataSourceClient = new DataSourceClient($this->xmpOptions, $this->soapOptions, $this->config);
        $compatibleTables = $DataSourceClient->getDataSourceCompatibleTables($dataSourceId);

        if (in_array($dataSourceTableName, $compatibleTables)) {
            return $dataSourceTableName;
        }

        if (in_array('recipients', $compatibleTables)) {
            return 'recipients';
        }

        if (in_array('Recipients', $compatibleTables)) {
            return 'Recipients';
        }

        if (isset($compatibleTables[0])) {
            return $compatibleTables[0];
        }

        return false;
    }

    /**
     * @param int $ticketId
     * @param int $dataSourceId
     * @param RecipientsInfo $recipientsInfo
     * @return bool|null
     * @throws SoapFault
     */
    public function addRIByID(int $ticketId, int $dataSourceId, RecipientsInfo $recipientsInfo): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->AddRIByID()
            ->setInTicketID($ticketId)
            ->setInDataSourceID($dataSourceId)
            ->setInRIInfo($recipientsInfo);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->AddRIByID($Request);

        return $result->getAddRIByIDResult();
    }

    /**
     *  No Recipients Information list e.g. when an ADOR Objects or Variables are customized with fixed values for a single recipient
     *
     * @param $ticketId
     * @param RecipientsInfo $recipientsInfo
     * @param Connection|null $connection
     * @return bool|null
     * @throws SoapFault
     */
    public function addRI($ticketId, RecipientsInfo $recipientsInfo, Connection $connection = null): ?bool
    {
        if (empty($connection)) {
            $connection = (new Connection())->setM_Type('NONE')->setM_ConnectionString('')->setM_AdditionalInfo('');
        }

        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->AddRI()
            ->setInTicketID($ticketId)
            ->setInRIInfo($recipientsInfo)
            ->setInConnection($connection);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->AddRI($Request);

        return $result->getAddRIResult();
    }

    /**
     * @param $ticketId
     * @return bool|null
     * @throws SoapFault
     */
    public function removeAllSchemaDataSources($ticketId): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->RemoveAllSchemaDataSources()
            ->setInTicketID($ticketId);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->RemoveAllSchemaDataSources($Request);

        return $result->getRemoveAllSchemaDataSourcesResult();
    }

    /**
     * @param $ticketId
     * @param $planId
     * @param $variables
     * @return array
     * @throws SoapFault
     */
    public function setCustomisationVariables($ticketId, $planId, $variables): array
    {
        $planVariables = (new PlanClient())->getVariables($planId);
        $results = [];
        foreach ($variables as $k => $v) {
            if ($v === null) {
                continue;
            }
            if ($planVariables[$k]['extended_type'] == 'NUMBER') {
                $v = intval($v);
                $expressionType = false;
            } else {
                $expressionType = true;
            }

            $customisation = (new Customization())
                ->setM_Name($k)
                ->setM_Expression($v)
                ->setM_IOType('R')
                ->setM_Type('VAR');

            $Request = $this->RequestFabricator->JobTicket_SSP()
                ->SetCustomization()
                ->setInTicketID($ticketId)
                ->setInCustomization($customisation)
                ->setExpressionAsValue($expressionType);
            $Service = $this->ServiceFabricator->JobTicket_SSP();
            $results[] = $Service->SetCustomization($Request)->getSetCustomizationResult();
        }

        return $results;
    }

    /**
     * @param $ticketId
     * @param $planId
     * @param $adors
     * @return array
     * @throws SoapFault
     */
    public function setCustomisationAdors($ticketId, $planId, $adors): array
    {
        $planAdors = (new PlanClient())->getADORs($planId);
        $results = [];
        foreach ($adors as $k => $v) {
            if ($v === null) {
                continue;
            }
            $customisation = (new Customization())
                ->setM_Name($k)
                ->setM_Expression($v)
                ->setM_IOType('R')
                ->setM_Type('ADOR');

            $Request = $this->RequestFabricator->JobTicket_SSP()
                ->SetCustomization()
                ->setInTicketID($ticketId)
                ->setInCustomization($customisation)
                ->setExpressionAsValue(true);
            $Service = $this->ServiceFabricator->JobTicket_SSP();
            $results[] = $Service->SetCustomization($Request)->getSetCustomizationResult();
        }

        return $results;
    }

    /**
     * @param $ticketId
     * @param array|int $assetSourceIds
     * @return bool|null
     * @throws SoapFault
     */
    public function setAssetSourcesByID($ticketId, array|int $assetSourceIds): ?bool
    {
        if (is_numeric($assetSourceIds) && is_int($assetSourceIds)) {
            $assetSourceIds = [$assetSourceIds];
        }

        $assetSources = new ArrayOfString();
        $assetSources->setString($assetSourceIds);

        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetAssetSourcesByID()
            ->setInTicketID($ticketId)
            ->setInAssetSourceIDArray($assetSources);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetAssetSourcesByID($Request);

        return $result->getSetAssetSourcesByIDResult();
    }

    /**
     * @param $ticketId
     * @param int $destinationID
     * @return bool|null
     * @throws SoapFault
     */
    public function addDestinationByID($ticketId, int $destinationID): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->AddDestinationByID()
            ->setInTicketID($ticketId)
            ->setInDestinationID($destinationID);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->AddDestinationByID($Request);

        return $result->getAddDestinationByIDResult();
    }

    /**
     *
     * 1 - PRINT A print job
     * 2 - PROOF A proof job
     * 3 - DOWNLOAD_DOCUMENT A Document Package (DPKG) creation job
     * 4 - DOWNLOAD_CAMPAIGN A Campaign Package (CPKG) creation job
     * 5 - PROOF_SET A Proof Set generation job
     * 8 - RECORD_SET An Interactive Content Port (ICP) job
     * 10 - EMAIL_MARKETING An email batch submission job
     * 11 - EMAIL_MARKETING_TEST An email batch test job
     * 13 - EMAIL_PREFLIGHT An email batch preflight job
     *
     * @param $ticketId
     * @param string $jobType
     * @return bool|null
     * @throws SoapFault
     */
    public function setJobType($ticketId, string $jobType): ?bool
    {
        $allowedTypes = [
            "PRINT",
            "PROOF",
            "DOWNLOAD_DOCUMENT",
            "DOWNLOAD_CAMPAIGN",
            "PROOF_SET",
            "RECORD_SET",
            "EMAIL_MARKETING",
            "EMAIL_MARKETING_TEST",
            "EMAIL_PREFLIGHT",
        ];

        if (!in_array($jobType, $allowedTypes)) {
            return false;
        }

        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetJobType()
            ->setInTicketID($ticketId)
            ->setInJobType($jobType);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetJobType($Request);

        return $result->getSetJobTypeResult();
    }

    /**
     * Set a priority level
     *
     * "Lowest", "VeryLow", "Low", "Normal", "AboveNormal", "High", "VeryHigh", "Highest".
     *
     * @param $ticketId
     * @param string $jobPriority
     * @return bool|null
     * @throws SoapFault
     */
    public function setJobPriority($ticketId, string $jobPriority): ?bool
    {
        $allowedTypes = [
            "Lowest",
            "VeryLow",
            "Low",
            "Normal",
            "AboveNormal",
            "High",
            "VeryHigh",
            "Highest",
        ];

        if (!in_array($jobPriority, $allowedTypes)) {
            return false;
        }

        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetJobPriority()
            ->setInTicketID($ticketId)
            ->setInJobPriority($jobPriority);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetJobPriority($Request);

        return $result->getSetJobPriorityResult();
    }

    /**
     * Sets the specified job ticket with the job output type: Proof Set, VPS, PDF etc.
     *
     * PROOF_SET A Proof Set job output format.
     * VPS A VPS job output format.
     * PDF A PDF job output format.
     * PDFO An optimized PDF job output format. This file will be automatically treated as a PDF for productions of five records or less.
     * PDFVT1
     * PPML A PPML job output format.
     * VDX A VDX job output format.
     * VIPP A VIPP job output format.
     * JPG A JPEG job output format.
     * PS A PostScript job output format.
     * RECORD_SET An output type required in order to create a Port.
     *
     * @param $ticketId
     * @param string $outputType
     * @return bool|null
     * @throws SoapFault
     */
    public function setOutputType($ticketId, string $outputType): ?bool
    {
        $allowedTypes = [
            "PROOF_SET",
            "VPS",
            "PDF",
            "PDFO",
            "PDFVT1",
            "PPML",
            "VDX",
            "VIPP",
            "JPG",
            "PNG",
            "PS",
            "RECORD_SET",
        ];

        $outputType = strtoupper($outputType);

        if (!in_array($outputType, $allowedTypes)) {
            return false;
        }

        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetOutputType()
            ->setInTicketID($ticketId)
            ->setInType($outputType);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetOutputType($Request);

        return $result->getSetOutputTypeResult();
    }

    /**
     * Sets the specified job ticket with the name of the job output file.
     *
     * @param $ticketId
     * @param string $fileName
     * @return bool|null
     * @throws SoapFault
     */
    public function setOutputFileName($ticketId, string $fileName): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetOutputFileName()
            ->setInTicketID($ticketId)
            ->setInFileName($fileName);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetOutputFileName($Request);

        return $result->getSetOutputFileNameResult();
    }

    /**
     * Sets the specified job ticket with the folder to which the job is to be saved.
     *
     * @param $ticketId
     * @param string $folderName
     * @return bool|null
     * @throws SoapFault
     */
    public function setOutputFolder($ticketId, string $folderName): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetOutputFolder()
            ->setInTicketID($ticketId)
            ->setInFolder($folderName);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetOutputFolder($Request);

        return $result->getSetOutputFolderResult();
    }

    /**
     * Sets the specified job ticket with the job output's media type: print, Web or email.
     *
     * Media Value Description
     * 1 Print
     * 2 Web
     * 4 Email
     *
     * @param $ticketId
     * @param string $outputMedia
     * @return bool|null
     * @throws SoapFault
     */
    public function setOutputMedia($ticketId, string $outputMedia): ?bool
    {
        $allowedTypes = [1, 2, 4];

        if (!in_array($outputMedia, $allowedTypes)) {
            return false;
        }

        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetOutputMedia()
            ->setInTicketID($ticketId)
            ->setInMedia($outputMedia);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetOutputMedia($Request);

        return $result->getSetOutputMediaResult();
    }

    /**
     * Set a single Output Parameter as key/value
     *
     * @param $ticketId
     * @param $paramName
     * @param $paramValue
     * @return bool|null
     * @throws SoapFault
     */
    public function setOutputParameter($ticketId, $paramName, $paramValue): ?bool
    {
        $paramToSet = new parameter;
        $paramToSet = $paramToSet->setM_Name($paramName);
        $paramToSet = $paramToSet->setM_Value($paramValue);

        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetOutputParameter()
            ->setInTicketID($ticketId)
            ->setInParam($paramToSet);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetOutputParameter($Request);

        return $result->getSetOutputParameterResult();
    }

    /**
     * Set a multiple Output Parameters as keys/values in an array
     *
     * @param $ticketId
     * @param $params
     * @return bool|null
     * @throws SoapFault
     */
    public function setOutputParameters($ticketId, $params): ?bool
    {
        $paramsToSet = new ArrayOfParameter;
        $paramsFormatted = [];
        foreach ($params as $paramName => $paramValue) {
            $paramToSet = new parameter;
            $paramToSet = $paramToSet->setM_Name($paramName);
            $paramToSet = $paramToSet->setM_Value($paramValue);
            $paramsFormatted[] = $paramToSet;
        }
        $paramsToSet = $paramsToSet->setParameter($paramsFormatted);

        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetOutputParameters()
            ->setInTicketID($ticketId)
            ->setInParams($paramsToSet);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetOutputParameters($Request);

        return $result->getSetOutputParametersResult();
    }

    /**
     * Set a multiple Output Parameters as keys/values in an array
     *
     * @param $ticketId
     * @param bool $split
     * @param bool $merge
     * @return bool|null
     * @throws SoapFault
     */
    public function setAutomaticSubSplitAndMerge($ticketId, bool $split, bool $merge): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetAutomaticSubSplitAndMerge()
            ->setInTicketID($ticketId)
            ->setInEnableAutomaticSubSplit($split)
            ->setInEnableAutomaticMerge($merge);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetAutomaticSubSplitAndMerge($Request);

        return $result->getSetAutomaticSubSplitAndMergeResult();
    }

    /**
     * Set how the job is split into batches
     *
     * @param $ticketId
     * @param int|string $splitNumber the number of batches or records
     * @param int|string $splitType 0 defines the number of records in each batch. 1 defines the number of batches in this job.
     * @param int $origFrom
     * @param int $origTo
     * @param bool $mergeOutput
     * @return bool|null
     * @throws SoapFault
     */
    public function setSplittedJobInfo($ticketId, int|string $splitNumber, int|string $splitType, int $origFrom = null, int $origTo = null, bool $mergeOutput = false): ?bool
    {
        $splitNumber = intval($splitNumber);

        if (!is_numeric($splitType)) {
            if (strtolower($splitType) === 'records') {
                $splitType = 0;
            } else {
                $splitType = 1;
            }
        } else {
            $splitType = intval($splitType);
        }

        if (empty($origFrom)) {
            $origFrom = $this->getRIFrom($ticketId);
            if ($origFrom === 0) {
                $origFrom = 1;
            }
        }

        if (empty($origTo)) {
            $origTo = $this->getRITo($ticketId);
            if ($origTo === 0) {
                $origTo = -1;
            }
        }

        //dump($ticketId, $splitNumber, $splitType, $origFrom, $origTo, $mergeOutput);

        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetSplittedJobInfo()
            ->setInTicketID($ticketId)
            ->setInSplitNum($splitNumber)
            ->setInSplitType($splitType)
            ->setInOrigFrom($origFrom)
            ->setInOrigTo($origTo)
            ->setInMergeOutput($mergeOutput);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetSplittedJobInfo($Request);

        return $result->getSetSplittedJobInfoResult();
    }

    /**
     * The data provided by XMPie is in an array/JSON format
     *
     * @param $ticketId
     * @param int $dataSourceID
     * @return bool|null
     * @throws SoapFault
     */
    public function setJobReportCallbackURL($ticketId, string $url): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetJobReportCallbackURL()
            ->setInTicketID($ticketId)
            ->setInJobReportCallbackURL($url);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetJobReportCallbackURL($Request);

        return $result->getSetJobReportCallbackURLResult();
    }

    /**
     * The data provided by XMPie is XML formatted
     *
     * @param $ticketId
     * @param int $dataSourceID
     * @return bool|null
     * @throws SoapFault
     */
    public function setJobReportingWebService($ticketId, string $url): ?bool
    {
        $Request = $this->RequestFabricator->JobTicket_SSP()
            ->SetJobReportingWebService()
            ->setInTicketID($ticketId)
            ->setInWebServiceURL($url);
        $Service = $this->ServiceFabricator->JobTicket_SSP();
        $result = $Service->SetJobReportingWebService($Request);

        return $result->getSetJobReportingWebServiceResult();
    }

    /**
     * Applies the units to the BLEED parameters
     *
     * @param $triggerFile
     * @return array
     */
    public function applyBleedUnitsToTriggerFile($triggerFile): array
    {
        $Geo = new PDFGeometry();

        if (!isset($triggerFile['JobTicket']['OutputParameter_BLEED_UNITS'])) {
            return $triggerFile;
        }

        $units = $triggerFile['JobTicket']['OutputParameter_BLEED_UNITS'];

        if (empty($units)) {
            return $triggerFile;
        }

        $units = strtolower($units);

        if (is_numeric($triggerFile['JobTicket']['OutputParameter_BLEED_TOP'])) {
            $triggerFile['JobTicket']['OutputParameter_BLEED_TOP'] = ($Geo->convertUnit($triggerFile['JobTicket']['OutputParameter_BLEED_TOP'], $units, 'pts', 10));
        }
        if (is_numeric($triggerFile['JobTicket']['OutputParameter_BLEED_BOTTOM'])) {
            $triggerFile['JobTicket']['OutputParameter_BLEED_BOTTOM'] = ($Geo->convertUnit($triggerFile['JobTicket']['OutputParameter_BLEED_BOTTOM'], $units, 'pts', 10));
        }
        if (is_numeric($triggerFile['JobTicket']['OutputParameter_BLEED_LEFTORINSIDE'])) {
            $triggerFile['JobTicket']['OutputParameter_BLEED_LEFTORINSIDE'] = ($Geo->convertUnit($triggerFile['JobTicket']['OutputParameter_BLEED_LEFTORINSIDE'], $units, 'pts', 10));
        }
        if (is_numeric($triggerFile['JobTicket']['OutputParameter_BLEED_RIGHTOROUTSIDE'])) {
            $triggerFile['JobTicket']['OutputParameter_BLEED_RIGHTOROUTSIDE'] = ($Geo->convertUnit($triggerFile['JobTicket']['OutputParameter_BLEED_RIGHTOROUTSIDE'], $units, 'pts', 10));
        }
        if (is_numeric($triggerFile['JobTicket']['OutputParameter_BLEED_X'])) {
            $triggerFile['JobTicket']['OutputParameter_BLEED_X'] = ($Geo->convertUnit($triggerFile['JobTicket']['OutputParameter_BLEED_X'], $units, 'pts', 10));
        }
        if (is_numeric($triggerFile['JobTicket']['OutputParameter_BLEED_Y'])) {
            $triggerFile['JobTicket']['OutputParameter_BLEED_Y'] = ($Geo->convertUnit($triggerFile['JobTicket']['OutputParameter_BLEED_Y'], $units, 'pts', 10));
        }

        return $triggerFile;
    }

    /**
     * @return array
     */
    public function listAllOutputParameters(): array
    {
        return [
            //direct XML params you can set
            "EMBEDED_ELEMENTS",
            "EMBED_RESOURCES",
            "EMBED_FONTS",
            "CENTER_PAGE",
            "ASSETS_POLICY",
            "FONTS_POLICY",
            "OVERFLOW_POLICY",
            "STYLE_POLICY",
            "BW_POLICY",
            "OUTPUT_SIZE_LIMIT_POLICY",
            "BLEED_TOP",
            "BLEED_BOTTOM",
            "BLEED_LEFTORINSIDE",
            "BLEED_RIGHTOROUTSIDE",
            "BLEED_USE_DOCUMENT_DEF",
            "COPY_TYPE",
            "COPY_NUM",
            "PACK_ASSETS",
            "PACK_RESOURCES",
            "ASSET_STORAGE",
            "RESOURCE_STORAGE",
            "OUTPUT_RES",
            "PDF_MULTI_SINGLE_RECORD",
            "INTERACTIVE_PDF_ELEMENTS",
            "VERSION",
            "NATIVE_PDF_OPTIONS",
            "OUTPUT_TYPE",
            "EMBED_PPML_RESOURCES_IN_STREAM",

            //from API you can set,
            "ASSETS_POLICY",
            "BLEED_X",
            "BLEED_Y",
            "BLEED_USE_DOCUMENT_DEF",
            "COPY_NUM",
            "COPY_TYPE",
            "DS_PER_DOC",
            "EMBEDED_ELEMENTS",
            "EMBED_FONTS",
            "EMBED_PPML_RESOURCES_IN_STREAM",
            "EMBED_RESOURCES",
            "FONTS_POLICY",
            "LABEL_MASTER_PAGES",
            "METADATA",
            "NATIVE_PDF_OPTIONS",
            "OUTPUT_VPC_STUB_PATH",
            "OVERFLOW_POLICY",
            "PACK_ASSETS",
            "PACK_RESOURCES",
            "PDF_MULTI_SINGLE_RECORD",
            "STYLE_POLICY",
            "USE_GLOBAL_CACHING",
            "VERSION",
            "OUTPUT_RES",
            "FILE_NAME_ADOR",
            "SEPARATE_UNIQUE_CONTENT",
            "SEPARATE_REUSABLE_CONTENT",
            "COPY_ASSETS_TO_REMOTE_DESTINATION",
            "COPY_RESOURCES_TO_REMOTE_DESTINATION",
            "IGNORE_FLATTENING_POLICY",
            "USE_UPRODUCE_GLOBAL_CACHING",
            "PACKAGE_COMPONENT_MASK",
            "REQUIRE_INDESIGN_RENDERING",
            "USE_RIP_TRANSPARENCY",
        ];
    }


}


/*
ASSETS_POLICY                           Determines how uProduce handles missing Assets (dynamic images used in ADOR Objects) during production. "0" means "Ignore the error"; "1" means "Skip records that are missing Assets" (i.e. move on to process the next record. The output file will not include a customer communication for skipped records); "2" means "Fail the job". Default is "0".
BLEED_X                                 (Double) adds the input value to each of the horizontal edges of a page (the value is added to both sides, so the dimensions should be larger by twice this number), thereby widening the page area without drawing on it. Default is "0".
BLEED_Y                                 (Double) adds the input value to each of the vertical edges of a page (the value is added to both sides, so the dimensions should be larger by twice this number), thereby widening the page area without drawing on it. Default is "0".
BLEED_USE_DOCUMENT_DEF                  Determines whether to apply the bleed settings defined in the InDesign document. Set "1" if you wish to apply the InDesign bleed settings to your Document. Otherwise, set "0".
COPY_NUM                                Used together with COPY_TYPE (described below), to determine how many copies are to be produced per-record. The input value is either a fixed number of copies to be produced per-record (in case all recipients are to receive the same number or copies); or the name of an ADOR Object, whose value should be reevaluated per-record, to determine the number of copies to be produced for each recipient. Default is "1".
COPY_TYPE                               Used together with COPY_NUM (described above), to determine how many copies are to be produced per-record. A value of "0" means that the value in COPY_NUM is a fixed number of copies; a value of "1" means that COPY_NUM defines an ADOR Object's name, whose value should be reevaluated per-record. Default is "0".
DS_PER_DOC                              (Boolean) In the context of PPML production, setting this flag to "true" creates a separate DOCUMENT_SET/JOB tag (depending on the PPML version of the required output) above each DOCUMENT tag; while "false" creates a single DOCUMENT_SET/JOB tag, which includes all DOCUMENT tags directly below it. Default is "false".
EMBEDED_ELEMENTS                        For print formats that allow referencing (PPML, VIPP and VPS), this parameter determines whether the description of images serving as Assets (dynamic images used in ADOR Objects) should be embedded in the print stream or merely referenced. The value "0" indicates "Embed"; the value "2" indicates "Do not embed/refer". Default is "Embed". Note:Referencing may sometimes be refused for EMBEDED_ELEMENTS and EMBED_RESOURCES. This happens when referencing an image, and trusting the RIP mechanisms to embed the image content later on, which results in corrupted output.
EMBED_FONTS                             Defines the required behavior in case a missing font is encountered during production. "0" means "Ignore the error", "2" means "Fail the job" (the option to skip a record, which is available when setting the ASSETS_POLICY parameter, is not supported for missing fonts). Note:If "EMBED_FONTS" is set to "do not embed", this policy is not applied when the missing font is only used to compensate for other missing fonts in image descriptions.
EMBED_PPML_RESOURCES_IN_STREAM          In the context of PPML production determines whether the PPML is accompanied by a resources file (*.res file), or whether PPML 2.0 syntax is used for embedding supplied resources in stream. The value of "true" or "1" marks - embed (i.e. there will not be a .res file), and the value of "false" or "0" marks - do not embed (i.e. the PPML file is accompanied by a .res file). Default is "Do not embed".
EMBED_RESOURCES                         For print formats that allow referencing (PPML, VIPP and VPS), this parameter determines whether for images serving as Resources (static images used in the Dynamic Document Template) their description should be embedded in the print stream or merely referenced. The value of 0 indicates "embed"; the value of 2 indicates "do not embed/refer". Default is "embed". Note:Referencing may sometimes be refused for EMBEDED_ELEMENTS and EMBED_RESOURCES. This happens when referencing an image, and trusting the RIP mechanisms to embed the image content later on will result in corrupted output.
FONTS_POLICY                            Determines how uProduce handles missing fonts during production. "0" means "Ignore the error", "2" means "Fail the job" (the option to skip records, which is available when setting the "ASSETS_POLICY" parameter, is not supported for missing fonts). Default is "0". Note:If EMBED_FONTS is set to "Do not embed", this policy is not applied to missing fonts is they are used only to compensate for other missing fonts in image descriptions.
LABEL_MASTER_PAGES                      (Boolean) In the context of PPML production, this flag is used to apply Xeikon master tagging. This tagging is used for optimizing print production in relation to Xeikon company machines. In general, whenever the target is Xeikon, this flag should be set to "true". Default is "false".
METADATA                                (Boolean) In the context of PPML production, setting this flag to "true" adds METADATA tags to each DOCUMENT tag, while "true" creates a single METADATA tag containing all DOCUMENT tags. Note:Reserved for future implementation. This parameter should be set to "false".
NATIVE_PDF_OPTIONS                      The name of the Adobe InDesign conversion settings file, used to export PDFs. This settings file is used when PDF export is utilized by XMPie production. This happens in VDX production and in native PDF production ("native PDF" is when XMPie creates the PDF directly, instead of fist generating a PostScript file and only then converting it to PDF). The default value is "XMPieQualityHigh". For proof, set the value to "XMPiEQualityProof" and use the "SetDistillJobOptionName" method to set the name of the PDF Conversion Settings file.
OUTPUT_VPC_STUB_PATH                    Required in the context of VPC file creation (when either PACK_ASSETS or PACK_RESOURCES are set to "true"). Marks the path (a Windows/Mac/Unix path) to the stub file, which is to be used with VPC packing for PS, PDF and PPML print formats. This parameter may be omitted when VPC is not required.
OVERFLOW_POLICY                         Determines how uProduce handles missing text overflow during production. This includes both regular story overflows and table cells overflow. The value of "0" means "Ignore the error"; "2" means "Fail the job" (the option to skip records, which is available when setting the "ASSETS_POLICY" parameter, is not supported for text overflow). Default is "0".
PACK_ASSETS                             (Boolean) Marks VPC file creation. Set this parameter to "true" to pack referenced Assets (dynamic images used in ADOR Objects) in the VPC file.
PACK_RESOURCES                          (Boolean) Marks VPC file creation. Set this parameter to "true" to pack referenced Resources (static images used in the Dynamic Document Template) in the VPC.
PDF_MULTI_SINGLE_RECORD                 (Boolean) "true" generates a separate PDF file for each record that is produced (which is useful for electronic distribution of the output files); "false" generates a single PDF file that contains the pages produced for all records. Default is "false".
STYLE_POLICY                            Determines how uProduce handles missing dynamic styles during production. 0 means "ignore", 1 means "Skip record that are missing dynamic styles" (i.e. move on to process the next record. The output file will not include a customer communication for skipped records); "2" means "Fail the job". Default is "0".
USE_GLOBAL_CACHING                      Determines whether to use the Repeat Jobs feature, which optimizes the production speed of Documents that are processed repeatedly.
                                        The Repat Jobs feature identifies parts of the Document that are common to multiple recipients (such as static text), and uses uProduce Global Caching to save this content along with uProduce's temporary files.
                                        The next time the Document is processed, this common content is reused. In addition, any new content created by the current job is be added to uProduce's temporary files.
                                        This feature supports the following Print Output file formats: PPML, Scitex VPS, Xerox VIPP, PostScript and Optimized PDF.
                                        (Boolean) Set this parameter to "true" to enable global caching (the default option), or to "false" to disable global caching.
VERSION                                 (Double) In the context of PPML production, this parameter determines the version of the emitted PPML. Set this parameter to either "1.5" or "2.1". Default is "1.5".
OUTPUT_RES                              (Integer) When the output type is JPEG or PNG, this parameter determines the resolution of the output file.
FILE_NAME_ADOR                          Uses an ADOR Object to set the name of the output file. The first record represented in the output file is used to evaluate the specified ADOR Object. The result is used as the name of the output file.
SEPARATE_UNIQUE_CONTENT                 Creates external files that hold the Document's unique elements, which are referenced by the main output file, to optimize the Document's processing by certain print controllers.
SEPARATE_REUSABLE_CONTENT               Creates external files that hold the Document's reusable elements, which are referenced by the main output file, to optimize the Document's processing by certain print controllers.
COPY_ASSETS_TO_REMOTE_DESTINATION       Copies the Assets (dynamic images used in ADOR Objects) to a remote destination. Relevant only for VPS, VIPP and PPML output formats.
COPY_RESOURCES_TO_REMOTE_DESTINATION    Copies Resources (static images used in the Dynamic Document Template) to a remote destination. Relevant only for VPS, VIPP and PPML output formats
IGNORE_FLATTENING_POLICY                Determine how to implement the X-DOT technology in your Document, or choose not to use X-DOT. Available options are:
                                        "0" - Use X-DOT.
                                        "1" - Ignore X-DOT as needed. X-DOT will not be applied in this production run in the following cases:
                                        - The resulting mega object is reusable, where at least one of the atomic objects was fixed.
                                        - The resulting mega object is unique, where at least one of the atomic objects is reusable or was fixed.
                                        - The resulting mega object is reusable, where at least one of the atomic objects is reusable.
                                        "2" - Ignore X-DOT. No special effects will appear in the Print Output file of this production run.
                                        "3" - Fail job. The current production run will be failed in the following cases:
                                        - The resulting mega object is reusable, where at least one of the atomic objects was fixed.
                                        - The resulting mega object is unique, where at least one of the atomic objects is reusable or was fixed.
                                        - The resulting mega object is reusable, where at least one of the atomic objects is reusable.
                                        Note:If you ignore X-DOT, special affects will not be visible. All shadows, feathering and opacity effects will be removed. In addition, the transparent parts of transparent images will appear white (paper color).
USE_UPRODUCE_GLOBAL_CACHING             "1" - use uProduce global caching, or "0" - Don't use uProduce global caching , The default is "0".
PACKAGE_COMPONENT_MASK                  Determines which components are included in the Proof Set Package (PPKG). Note:This parameter applies only to Proof Set generation.This Bitmask parameter allows you to include one or more of the following options in the PPKG:
                                        "1" (the first bit is turned on) - includes the Proof Set Output file.
                                        "2" (the second bit is turned on) - includes the Asset files.
                                        "4" (the third bit is turned on) - compresses the PPKG components into a single PPKG (zipped) file.
                                        You may combine these options as needed, for example: to include both the Proof Set Output file and the Asset files, set this option to "3".
                                        The default value is "7", which creates a single, compressed PPKG file, containing the Proof Set Output file and the Assets.
REQUIRE_INDESIGN_RENDERING              Determines the method of rendering images in the XMPie print output.
                                        Set this parameter to false to allow XMPie to bypass InDesign handling of images. This option enables reference features in VPS, VIPP and PPML, and sometimes improves performance in all of these formats (VPS, PS, VIPP, PPML and PDF).
                                        Note:In this case, the image color profile is ignored.
                                        Set this parameter to true so that images will always be handled by InDesign, which uses the image color profile to render the output.
                                        Note:When choosing this option, images are always embedded in the Print Output file.
USE_RIP_TRANSPARENCY                    Determines how transparency is implemented in the PDF/VT-1 output.
                                        Set this parameter to false (default) so that all transparent content will be flattened when XMPie produces the Print Output file, without relying on the RIP’s ability to implement transparency. Choose this option in the following cases:
                                        - The RIP has no transparency implementation.
                                        - You are not sure if the RIP PDF/VT-1 implementation supports transparency.
                                        - The RIP transparency implementation is significantly slower than the production of flattened PDF/VT-1.
                                        Set this parameter to true so that transparency will be implemented when the RIP processes the Print Output file, using the PDF/VT-1 transparency definitions and avoiding X-DOT. Choose this option when you can rely on the RIP transparency implementation, in order to create a significantly more efficient Print Output file.
                                        Note:Before using this option, verify with your RIP vendor that the PDF/VT-1 transparency capabilities are implemented.
 */