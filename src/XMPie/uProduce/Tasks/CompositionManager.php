<?php

namespace App\XMPie\uProduce\Tasks;

use App\XMPie\uProduce\Clients\ClientFactory;

/**
 * Use this class to manage compositions.
 */
class CompositionManager extends BaseTasks
{
    public function __construct(array $xmpOptions, array $soapOptions, array $config)
    {
        parent::__construct($xmpOptions, $soapOptions, $config);

        $this->ClientFactory = new ClientFactory($xmpOptions, $soapOptions, $config);
    }

    /**
     * @param int $jobId
     * @return int|null
     * @throws \SoapFault
     */
    public function getJobStatus(int $jobId): ?int
    {
        $JC = $this->ClientFactory->JobClient();
        return $JC->getStatus($jobId);
    }

    /**
     * A job may have multiple files (i.e. pdf per record) so be careful when including download urls
     *
     * @param int $jobId
     * @param bool $includeDownloadUrl
     * @return array
     * @throws \SoapFault
     */
    public function getJobOutputResultsInfo(int $jobId, bool $includeDownloadUrl = false): array
    {
        $JC = $this->ClientFactory->JobClient();
        return $JC->getOutputResultsInfo($jobId, $includeDownloadUrl);
    }
}