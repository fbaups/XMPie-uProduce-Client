<?php

namespace App\XMPie\uProduce\Clients;

use SoapFault;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\ProductionServices\Production_SSP\ArrayOfProperty;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\ProductionServices\Production_SSP\ArrayOfString;

class ProductionClient extends BaseClient
{
    public function __construct(array $xmpOptions = [], array $soapOptions = [], array $config = [])
    {
        parent::__construct($xmpOptions, $soapOptions, $config);
    }

    /**
     * Main method to Submit a Job - production or proof.
     *
     * If you have configured the Ticket properly, no need to set the remaining parameters.
     *
     * @param $ticketId
     * @param ArrayOfProperty|null $inProps
     * @return int[]
     * @throws SoapFault
     */
    public function submitJobs($ticketId, ArrayOfProperty $inProps = null): array
    {
        if (empty($inProps)) {
            $inProps = new ArrayOfProperty;
        }

        $Request = $this->RequestFabricator->Production_SSP()
            ->SubmitJobs()
            ->setInJobTicket($ticketId)
            ->setInProps($inProps);
        $Service = $this->ServiceFabricator->Production_SSP();
        $result = $Service->SubmitJobs($Request);

        $jobIds = [];
        /** @var ArrayOfString $jobs */
        $jobs = $result->getSubmitJobsResult();
        foreach ($jobs as $job) {
            $jobIds[] = intval($job);
        };

        return $jobIds;
    }


    /**
     * Wrapper function
     * - can do single or parallel process
     * - set the batch processing
     *
     * @param $ticketId
     * @param string $type 'batches' or 'records'
     * @param int $count number of [total batches] or [records per batch]
     * @param bool $isParallel
     * @return int[]
     * @throws SoapFault
     */
    public function submitJobsAsBatchesFlagParallel($ticketId, string $type, int $count, bool $isParallel = false): array
    {
        $JobTicketClient = new JobTicketClient();
        if ($isParallel) {
            $JobTicketClient->setAutomaticSubSplitAndMerge($ticketId, true, true);
        } else {
            $JobTicketClient->setAutomaticSubSplitAndMerge($ticketId, false, true);
        }

        $from = $JobTicketClient->getRIFrom($ticketId);
        if ($from === 0) {
            $from = 1;
        }
        $to = $JobTicketClient->getRITo($ticketId);
        if ($to === 0) {
            $to = -1;
        }

        if (strtolower($type) === 'records') {
            $type = 0;
        } elseif (strtolower($type) === 'batches') {
            $type = 1;
        }

        $JobTicketClient->setSplittedJobInfo($ticketId, $count, $type, $from, $to, false);

        return $this->submitJobs($ticketId);
    }


    /**
     * Wrapper function
     * Flag job as single-thread or multi-thread processing
     *
     * @param $ticketId
     * @param bool $isParallel
     * @return int[]
     * @throws SoapFault
     */
    public function submitJobsFlagParallel($ticketId, bool $isParallel = false): array
    {
        $JobTicketClient = new JobTicketClient();
        if ($isParallel) {
            $JobTicketClient->setAutomaticSubSplitAndMerge($ticketId, true, true);
        } else {
            $JobTicketClient->setAutomaticSubSplitAndMerge($ticketId, false, true);
        }

        return $this->submitJobs($ticketId);
    }


    /*
     * Deprecated functions below this line...
     * They are here for historical purposes only.
     */


    /**
     * Submit a Job. If you have configured the Ticket properly, no need to set the remaining parameters.
     *
     * @param $ticketId
     * @param string $priority
     * @param string $touchPointId
     * @param ArrayOfProperty|null $inProps
     * @return string|null
     * @throws SoapFault
     * @deprecated use $this->submitJobs() instead
     */
    public function submitJob($ticketId, string $priority = '', string $touchPointId = '', ArrayOfProperty $inProps = null): ?string
    {
        if (empty($inProps)) {
            $inProps = new ArrayOfProperty;
        }

        $Request = $this->RequestFabricator->Production_SSP()
            ->SubmitJob()
            ->setInJobTicket($ticketId)
            ->setInPriority($priority)
            ->setInTouchPointID($touchPointId)
            ->setInProps($inProps);
        $Service = $this->ServiceFabricator->Production_SSP();
        $result = $Service->SubmitJob($Request);

        return $result->getSubmitJobResult();
    }

    /**
     * Submit a Job with parallel processing. If you have configured the Ticket properly, no need to set the remaining parameters.
     *
     * @param $ticketId
     * @param string $priority
     * @param string $touchPointId
     * @param ArrayOfProperty|null $inProps
     * @return string|null
     * @throws SoapFault
     * @deprecated use $this->submitJobs() instead
     */
    public function submitJobWithParallelProcessing($ticketId, string $priority = '', string $touchPointId = '', ArrayOfProperty $inProps = null): ?string
    {
        if (empty($inProps)) {
            $inProps = new ArrayOfProperty;
        }

        $Request = $this->RequestFabricator->Production_SSP()
            ->SubmitJobWithParallelProcessing()
            ->setInJobTicket($ticketId)
            ->setInPriority($priority)
            ->setInTouchPointID($touchPointId)
            ->setInProps($inProps);
        $Service = $this->ServiceFabricator->Production_SSP();
        $result = $Service->SubmitJobWithParallelProcessing($Request);

        return $result->getSubmitJobWithParallelProcessingResult();
    }

    /**
     * Submit a Job with parallel processing. If you have configured the Ticket properly, no need to set the remaining parameters.
     *
     * @param $ticketId
     * @param int $splittingType
     * @param int $splittingInfo
     * @param string $priority
     * @param string $touchPointId
     * @param ArrayOfProperty|null $inProps
     * @return array|null
     * @throws SoapFault
     * @deprecated use $this->submitJobs() instead
     */
    public function submitSplittedJob($ticketId, int $splittingType = 1, int $splittingInfo = 2, string $priority = '', string $touchPointId = '', ArrayOfProperty $inProps = null): ?array
    {
        if (empty($inProps)) {
            $inProps = new ArrayOfProperty;
        }

        $Request = $this->RequestFabricator->Production_SSP()
            ->SubmitSplittedJob()
            ->setInJobTicket($ticketId)
            ->setInSplittingType($splittingType)
            ->setInSplittingInfo($splittingInfo)
            ->setInPriority($priority)
            ->setInTouchPointID($touchPointId)
            ->setInProps($inProps);
        $Service = $this->ServiceFabricator->Production_SSP();
        $result = $Service->SubmitSplittedJob($Request);

        $jobIds = [];
        /** @var ArrayOfString $jobs */
        $jobs = $result->getSubmitSplittedJobResult();
        foreach ($jobs as $job) {
            $jobIds[] = $job;
        };

        return $jobIds;
    }


}