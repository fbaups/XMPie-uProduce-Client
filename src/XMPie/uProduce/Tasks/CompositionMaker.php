<?php

namespace App\XMPie\uProduce\Tasks;

use App\InputTriggers\SpreadsheetTrigger;
use arajcany\ToolBox\Utility\Security\Security;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

/**
 * Use this class to make uProduce to compose a Job.
 */
class CompositionMaker extends BaseTasks
{

    public function __construct(array $xmpOptions, array $soapOptions, array $config)
    {
        parent::__construct($xmpOptions, $soapOptions, $config);
    }

    /**
     * Pass a trigger file and a uProduce composition will be executed.
     * An array of Job Numbers will be returned.
     *
     * Trigger file path can be a JSON or XLSX
     *
     * @param $triggerFile
     * @return false|int[]
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \SoapFault
     */
    public function produceFromTriggerFile($triggerFile): array|bool
    {
        if (!is_file($triggerFile)) {
            return false;
        }

        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($triggerFile);

        if (str_contains($mimeType, 'sheet') || str_contains($mimeType, 'office')) {
            $triggerFileContents = (new SpreadsheetTrigger())->convertTriggerToJson($triggerFile);
            $triggerFileContents = json_decode($triggerFileContents, JSON_OBJECT_AS_ARRAY);
        } else {
            $triggerFileContents = file_get_contents($triggerFile);
            $triggerFileContents = json_decode($triggerFileContents, JSON_OBJECT_AS_ARRAY);
        }

        //apply generic fixes to the triggerFile
        $triggerFileContents = $this->ClientFactory->JobTicketClient()->applyBleedUnitsToTriggerFile($triggerFileContents);

        $documentId = $triggerFileContents['Setup']['DocumentID'] ?? null;
        if (!$documentId) {
            return false;
        }

        $campaignId = $triggerFileContents['Setup']['CampaignID'] ?? null;
        if (!$campaignId) {
            $campaignId = $this->ClientFactory->DocumentClient()->getCampaignId($documentId);
        }

        $planId = $triggerFileContents['Setup']['PlanID'] ?? null;
        if (!$planId) {
            $planId = $this->ClientFactory->CampaignClient()->getPlanId($campaignId);
        }

        $accountId = $triggerFileContents['Setup']['AccountID'] ?? null;
        if (!$accountId) {
            $accountId = $this->ClientFactory->CampaignClient()->getAccountId($campaignId);
        }

        $ticketId = $this->ClientFactory->JobTicketClient()->createNewTicket();
        $this->ClientFactory->JobTicketClient()->setCampaignID($ticketId, $campaignId);
        $this->ClientFactory->JobTicketClient()->setDocumentID($ticketId, $documentId);
        $this->ClientFactory->JobTicketClient()->setPlanID($ticketId, $planId);

        $destinationId = $triggerFileContents['Setup']['DestinationID'] ?? null;
        if ($destinationId) {
            $this->ClientFactory->JobTicketClient()->addDestinationByID($ticketId, $destinationId);
        }

        $recipients = $triggerFileContents['Recipients'] ?? false;
        $rnd = Security::purl(6);
        $saveLocation = TMP . "recipients-{$rnd}.xlsx";
        if ($recipients) {
            $this->ClientFactory->DataSourceClient()->convertRawDataToDataFileForPlan($recipients, $planId, $saveLocation);
            $data = file_get_contents($saveLocation);
            $options = [
                'campaignId' => $campaignId,
                'fileName' => "recipients-{$rnd}.xlsx",
            ];
            $dataSourceId = $this->ClientFactory->DataSourceClient()->createXlsxDS($data, $options);
            unlink($saveLocation);
        } elseif ($triggerFileContents['DataSource']['DataSourceID']) {
            $validatedId = $this->ClientFactory->DataSourceClient()->validate($triggerFileContents['DataSource']['DataSourceID']);
            if ($validatedId) {
                $dataSourceId = $validatedId;
            } else {
                $dataSourceId = null;
            }
        } else {
            $dataSourceId = null;
        }

        if ($dataSourceId) {
            $this->ClientFactory->JobTicketClient()->setDataSourceID($ticketId, $dataSourceId);
        }


        $jobTicketProperties = $triggerFileContents['JobTicket'] ?? false;


        //Set the Recipient Info
        $startRecord = $jobTicketProperties['StartRecord'] ? intval($jobTicketProperties['StartRecord']) : false;
        if (empty($startRecord)) {
            $startRecord = -1;
        }
        $endRecord = $jobTicketProperties['EndRecord'] ? intval($jobTicketProperties['EndRecord']) : false;
        if (empty($endRecord)) {
            $endRecord = -1;
        }
        $options = [
            'dataSourceId' => $dataSourceId,
            'query' => $triggerFileContents['DataSource']['DataSourceQuery'],
            'planFilterName' => $triggerFileContents['DataSource']['DataSourcePlanFilterName'],
            'tableName' => $triggerFileContents['DataSource']['DataSourceTableName'],
            'startRecord' => $startRecord,
            'endRecord' => $endRecord,
        ];
        $this->ClientFactory->JobTicketClient()->setRIs($ticketId, $options);


        $outputFileName = $jobTicketProperties['OutputFileName'] ?? false;
        if ($outputFileName) {
            $this->ClientFactory->JobTicketClient()->setOutputFileName($ticketId, $outputFileName);
        }

        $jobType = $jobTicketProperties['JobType'] ?? false;
        if ($jobType) {
            $this->ClientFactory->JobTicketClient()->setJobType($ticketId, $jobType);
        }

        $jobPriority = $jobTicketProperties['JobPriority'] ?? false;
        if ($jobPriority) {
            $this->ClientFactory->JobTicketClient()->setJobPriority($ticketId, $jobPriority);
        }

        $outputType = $jobTicketProperties['OutputType'] ?? false;
        if ($outputType) {
            $this->ClientFactory->JobTicketClient()->setOutputType($ticketId, $outputType);
        }

        if (in_array(strtolower($outputType), ['jpg'])) {
            $jobTicketProperties['OutputParameter_OUTPUT_RES'] = $this->ClientFactory->DocumentClient()->reformatOutputResolution($documentId, $jobTicketProperties['OutputParameter_OUTPUT_RES']);
        }

        //set output parameters
        $outputParams = [];
        foreach ($jobTicketProperties as $k => $v) {
            if (empty($v)) {
                continue;
            }
            $searchString = "OutputParameter_";
            if (!str_starts_with($k, $searchString)) {
                continue;
            }
            $paramName = str_replace($searchString, "", $k);
            $paramValue = $v;
            $outputParams[$paramName] = $paramValue;
        }
        $this->ClientFactory->JobTicketClient()->setOutputParameters($ticketId, $outputParams);

        //set customisation VARIABLES
        $variables = $triggerFileContents['Variables'] ?? false;
        if ($variables) {
            $this->ClientFactory->JobTicketClient()->setCustomisationVariables($ticketId, $planId, $variables);
        }

        //set customisation ADORS
        $adors = $triggerFileContents['Adors'] ?? false;
        if ($adors) {
            $this->ClientFactory->JobTicketClient()->setCustomisationAdors($ticketId, $planId, $adors);
        }

        //job batching
        $outputBatchesOrRecords = $jobTicketProperties['OutputBatchesOrRecords'] ?? false;
        $outputBatchesOrRecordsCount = $jobTicketProperties['OutputBatchesOrRecordsCount'] ?? false;
        if ($outputBatchesOrRecords && $outputBatchesOrRecordsCount) {
            $this->ClientFactory->JobTicketClient()->setSplittedJobInfo($ticketId, $outputBatchesOrRecordsCount, $outputBatchesOrRecords);
        }

        //job splitting
        $automaticSubSplit = boolval($jobTicketProperties['AutomaticSubSplit']);
        $automaticMerge = boolval($jobTicketProperties['AutomaticMerge']);
        $this->ClientFactory->JobTicketClient()->setAutomaticSubSplitAndMerge($ticketId, $automaticSubSplit, $automaticMerge);

        //debug the ticket
        //dd($this->ClientFactory->JobTicketClient()->getTicket($ticketId,true));

        //produce the job
        $jobIds = $this->ClientFactory->ProductionClient()->submitJobs($ticketId);

        return $jobIds;
    }

}