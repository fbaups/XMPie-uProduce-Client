<?php

namespace App\InputTriggers;

class JsonTrigger extends BaseTrigger
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
            $savePath = $this->classDirectory . "../../tmp/Plan-{$planProperties['planID']}.json";
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
            $savePath = $this->classDirectory . "../../tmp/Document-{$documentProperties['documentID']}.json";
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
     */
    public function generateStubTrigger($recipients, $variables, $adors, $filename = null, array $setupOverrides = []): bool
    {
        $triggerMap = $this->getTriggerMap($recipients, $variables, $adors);

        $jsonTrigger = [];

        //setup
        foreach ($triggerMap['Setup'] as $setup) {
            if (isset($setupOverrides['DocumentID']) && $setup['name'] === 'DocumentID') {
                $value = $setupOverrides['DocumentID'];
            } else {
                $value = $setup['value'];
            }

            $jsonTrigger['Setup'][$setup['name']] = $value;
        }


        //datasource
        foreach ($triggerMap['DataSource'] as $dataSource) {
            $jsonTrigger['DataSource'][$dataSource['name']] = $dataSource['value'];
        }


        //recipients
        $rows = range(0, 2);
        foreach ($rows as $row) {
            foreach ($triggerMap['Recipients'] as $recipient) {
                $jsonTrigger['Recipients'][$row][$recipient ['name']] = '';
            }
        }


        //variables
        foreach ($triggerMap['Variables'] as $variable) {
            $jsonTrigger['Variables'][$variable['name']] = $variable['value'];
        }


        //adors
        foreach ($triggerMap['Adors'] as $ador) {
            $jsonTrigger['Adors'][$ador['name']] = $ador['value'];
        }


        //jobticket
        foreach ($triggerMap['JobTicket'] as $jobTicket) {
            $jsonTrigger['JobTicket'][$jobTicket['name']] = $jobTicket['value'];
        }


        //save the JSON
        if ($filename) {
            $savePath = $filename;
        } else {
            $savePath = $this->classDirectory . "../../tmp/sample.json";
        }

        file_put_contents($savePath, json_encode($jsonTrigger, JSON_PRETTY_PRINT));

        return is_file($savePath);
    }

}