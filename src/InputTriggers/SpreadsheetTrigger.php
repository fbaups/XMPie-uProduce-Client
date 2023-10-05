<?php

namespace App\InputTriggers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetTrigger extends BaseTrigger
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $planId
     * @param null $savePath
     * @return bool
     * @throws \SoapFault
     */
    public function generateStubTriggerFromPlan(int $planId, $savePath = null): bool
    {
        $planID = $this->uProduceClientFactory->PlanClient()->validate($planId);
        if (!$planID) {
            return false;
        }

        $planProperties = $this->uProduceClientFactory->PlanClient()->getAllProperties($planID);
        $planRecipients = $this->uProduceClientFactory->PlanClient()->getRecipientFields($planID);
        $planVariables = $this->uProduceClientFactory->PlanClient()->getVariables($planID);
        $planAdors = $this->uProduceClientFactory->PlanClient()->getADORs($planID);

        if (!$savePath) {
            $savePath = $this->classDirectory . "../../tmp/Plan-{$planProperties['planID']}.xlsx";
        }

        $setupOverrides = ['PlanID' => $planID];

        return $this->generateStubTrigger($planRecipients, $planVariables, $planAdors, $savePath, $setupOverrides);
    }

    /**
     * @param int $documentId
     * @param $savePath
     * @return bool
     * @throws \SoapFault
     */
    public function generateStubTriggerFromDocument(int $documentId, $savePath = null): bool
    {
        $documentId = $this->uProduceClientFactory->DocumentClient()->validate($documentId);
        if (!$documentId) {
            return false;
        }

        $documentProperties = $this->uProduceClientFactory->DocumentClient()->getAllProperties($documentId);
        $campaignID = $documentProperties['campaignID'];

        $planID = $this->uProduceClientFactory->CampaignClient()->getPlanId($campaignID);

        $planProperties = $this->uProduceClientFactory->PlanClient()->getAllProperties($planID);
        $planRecipients = $this->uProduceClientFactory->PlanClient()->getRecipientFields($planID);
        $planVariables = $this->uProduceClientFactory->PlanClient()->getVariables($planID);
        $planAdors = $this->uProduceClientFactory->PlanClient()->getADORs($planID);

        if (!$savePath) {
            $savePath = $this->classDirectory . "../../tmp/Document-{$documentProperties['documentID']}.xlsx";
        }

        $setupOverrides = ['DocumentID' => $documentId];

        return $this->generateStubTrigger($planRecipients, $planVariables, $planAdors, $savePath, $setupOverrides);
    }

    /**
     * Generate a trigger file. The Trigger file can be used to produce a Job.
     *
     * @param $recipients
     * @param $variables
     * @param $adors
     * @param $filename
     * @param array $setupOverrides
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generateStubTrigger($recipients, $variables, $adors, $filename = null, array $setupOverrides = []): bool
    {
        $triggerMap = $this->getTriggerMap($recipients, $variables, $adors);

        $spreadsheet = new Spreadsheet();
        $worksheetSetup = (new Worksheet())->setTitle('Setup');
        $worksheetDataSource = (new Worksheet())->setTitle('DataSource');
        $worksheetRecipients = (new Worksheet())->setTitle('Recipients');
        $worksheetVariables = (new Worksheet())->setTitle('Variables');
        $worksheetAdors = (new Worksheet())->setTitle('Adors');
        $worksheetJobTicket = (new Worksheet())->setTitle('JobTicket');

        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFFF0000'],
                ],
            ],
        ];

        //setup
        $worksheetSetup->setCellValue('A1', 'Name');
        $worksheetSetup->setCellValue('B1', 'Value');
        $worksheetSetup->setCellValue('C1', 'Hint');
        $row = 2;
        foreach ($triggerMap['Setup'] as $setup) {
            if (isset($setupOverrides['DocumentID']) && $setup['name'] === 'DocumentID') {
                $value = $setupOverrides['DocumentID'];
            } else {
                $value = $setup['value'];
            }

            $worksheetSetup->setCellValue("A{$row}", $setup['name']);
            $worksheetSetup->setCellValue("B{$row}", $value);
            $worksheetSetup->setCellValue("C{$row}", $setup['hint']);
            $row++;
        }
        foreach (range('A', 'C') as $column) {
            $worksheetSetup->getColumnDimension($column)->setAutoSize(true);
        }
        $spreadsheet->addSheet($worksheetSetup);


        //datasource
        $worksheetDataSource->setCellValue('A1', 'Name');
        $worksheetDataSource->setCellValue('B1', 'Value');
        $worksheetDataSource->setCellValue('C1', 'Hint');
        $row = 2;
        foreach ($triggerMap['DataSource'] as $dataSource) {
            $worksheetDataSource->setCellValue("A{$row}", $dataSource['name']);
            $worksheetDataSource->setCellValue("B{$row}", $dataSource['value']);
            $worksheetDataSource->setCellValue("C{$row}", $dataSource['hint']);
            $row++;
        }
        foreach (range('A', 'C') as $column) {
            $worksheetDataSource->getColumnDimension($column)->setAutoSize(true);
        }
        $spreadsheet->addSheet($worksheetDataSource);


        //recipients
        $col = 1;
        foreach ($triggerMap['Recipients'] as $recipient) {
            $cellValue = "{$recipient ['name']}[{$recipient ['hint']}]";
            $worksheetRecipients->setCellValue([$col, 1], $cellValue);
            $worksheetRecipients->getColumnDimensionByColumn($col)->setAutoSize(true);
            $col++;
        }
        $spreadsheet->addSheet($worksheetRecipients);


        //variables
        $worksheetVariables->setCellValue('A1', 'Name');
        $worksheetVariables->setCellValue('B1', 'Value');
        $worksheetVariables->setCellValue('C1', 'Hint');
        $row = 2;
        foreach ($triggerMap['Variables'] as $variable) {
            $worksheetVariables->setCellValue("A{$row}", $variable['name']);
            $worksheetVariables->setCellValue("B{$row}", $variable['value']);
            $worksheetVariables->setCellValue("C{$row}", $variable['hint']);
            $row++;
        }
        foreach (range('A', 'C') as $column) {
            $worksheetVariables->getColumnDimension($column)->setAutoSize(true);
        }
        $spreadsheet->addSheet($worksheetVariables);


        //adors
        $worksheetAdors->setCellValue('A1', 'Name');
        $worksheetAdors->setCellValue('B1', 'Value');
        $worksheetAdors->setCellValue('C1', 'Hint');
        $row = 2;
        foreach ($triggerMap['Adors'] as $ador) {
            $worksheetAdors->setCellValue("A{$row}", $ador['name']);
            $worksheetAdors->setCellValue("B{$row}", $ador['value']);
            $worksheetAdors->setCellValue("C{$row}", $ador['hint']);
            $row++;
        }
        foreach (range('A', 'C') as $column) {
            $worksheetAdors->getColumnDimension($column)->setAutoSize(true);
        }
        $spreadsheet->addSheet($worksheetAdors);


        //jobticket
        $worksheetJobTicket->setCellValue('A1', 'Name');
        $worksheetJobTicket->setCellValue('B1', 'Value');
        $worksheetJobTicket->setCellValue('C1', 'Hint');
        $row = 2;
        foreach ($triggerMap['JobTicket'] as $jobTicket) {
            $worksheetJobTicket->setCellValue("A{$row}", $jobTicket['name']);
            $worksheetJobTicket->setCellValue("B{$row}", $jobTicket['value']);
            $worksheetJobTicket->setCellValue("C{$row}", $jobTicket['hint']);
            $row++;
        }
        foreach (range('A', 'C') as $column) {
            $worksheetJobTicket->getColumnDimension($column)->setAutoSize(true);
        }
        $spreadsheet->addSheet($worksheetJobTicket);


        //save the workbook
        $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($spreadsheet->getSheetByName('Worksheet')));
        if ($filename) {
            $savePath = $filename;
        } else {
            $savePath = $this->classDirectory . "../../tmp/sample.xlsx";
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($savePath);

        return is_file($savePath);
    }


}