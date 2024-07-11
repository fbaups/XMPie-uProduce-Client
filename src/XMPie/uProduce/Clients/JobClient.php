<?php

namespace App\XMPie\uProduce\Clients;

use Cake\Utility\Xml;
use SoapFault;

class JobClient extends BaseClient
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
        $Request = $this->RequestFabricator->Job_SSP()
            ->IsExist()
            ->setInJobID($id);
        $Service = $this->ServiceFabricator->Job_SSP();
        $result = $Service->IsExist($Request);

        return $result->getIsExistResult();
    }

    /**
     * Validate the Job by Name or ID.
     * Will check that the current username/password can actually access the Job
     * Will return the Job ID or false
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
                if (isset($props['jobID'])) {
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
     * @return string[]|null
     * @throws SoapFault
     */
    public function getAllProperties($id): ?array
    {
        $Request = $this->RequestFabricator->Job_SSP()
            ->GetAllProperties()
            ->setInJobID($id);
        $Service = $this->ServiceFabricator->Job_SSP();
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
        $Request = $this->RequestFabricator->Job_SSP()
            ->Delete()
            ->setInJobID($id);
        $Service = $this->ServiceFabricator->Job_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }

    /**
     * @param $id
     * @return string|null
     * @throws SoapFault
     */
    public function getTicket($id): ?string
    {
        $Request = $this->RequestFabricator->Job_SSP()
            ->GetTicket()
            ->setInJobID($id);
        $Service = $this->ServiceFabricator->Job_SSP();
        $result = $Service->GetTicket($Request);

        $xmlString = $result->getGetTicketResult();
        $xmlString = str_replace("encoding='utf-16'", "encoding='utf-8'", $xmlString);

        return $xmlString;
    }

    /**
     * @param $id
     * @return array|null
     * @throws SoapFault
     */
    public function getTicketArray($id): ?array
    {
        $xmlString = $this->getTicket($id);
        $a = Xml::toArray(Xml::build($xmlString));
        return $a;
    }

    /**
     * @param $id
     * @param int $index
     * @return string|null
     * @throws SoapFault
     */
    public function getOutputResultBinaryFileStream($id, int $index = 0): ?string
    {
        $Request = $this->RequestFabricator->Job_SSP()
            ->GetOutputResultBinaryFileStream()
            ->setInJobID($id)
            ->setInResultIndex($index);
        $Service = $this->ServiceFabricator->Job_SSP();
        $result = $Service->GetOutputResultBinaryFileStream($Request);

        return $result->getGetOutputResultBinaryFileStreamResult();
    }

    /**
     * @param $id
     * @param int $index
     * @return int|null
     * @throws SoapFault
     */
    public function getOutputResultBinaryFileStreamSize($id, int $index = 0): ?int
    {
        $Request = $this->RequestFabricator->Job_SSP()
            ->GetOutputResultBinaryFileStreamSize()
            ->setInJobID($id)
            ->setInResultIndex($index);
        $Service = $this->ServiceFabricator->Job_SSP();
        $result = $Service->GetOutputResultBinaryFileStreamSize($Request);

        return intval($result->getGetOutputResultBinaryFileStreamSizeResult());
    }

    /**
     * @param $id
     * @param int $index
     * @param bool $inline
     * @return string|null
     * @throws SoapFault
     */
    public function getOutputResultDownloadURL($id, int $index = 0, bool $inline = true): ?string
    {
        $Request = $this->RequestFabricator->Job_SSP()
            ->GetOutputResultDownloadURL()
            ->setInJobID($id)
            ->setInResultIndex($index)
            ->setInIsInline($inline)
            ->setInReturnInternalURL(false);
        $Service = $this->ServiceFabricator->Job_SSP();
        $result = $Service->GetOutputResultDownloadURL($Request);

        return $result->getGetOutputResultDownloadURLResult();
    }

    /**
     * Get the Job Status by Job ID
     *
     * Value    Description
     * 1        Waiting
     * 2        In progress
     * 3        Completed
     * 4        Failed
     * 5        Aborting
     * 6        Aborted
     * 7        Deployed
     * 8        Suspended
     *
     * @param $id
     * @return int|null
     * @throws SoapFault
     */
    public function getStatus($id): ?int
    {
        $Request = $this->RequestFabricator->Job_SSP()
            ->GetStatus()
            ->setInJobID($id);
        $Service = $this->ServiceFabricator->Job_SSP();
        $result = $Service->GetStatus($Request);

        return intval($result->getGetStatusResult());
    }

    /**
     * @param $id
     * @param bool $includeDownloadUrl can be expensive if multiple files
     * @return array|false
     * @throws SoapFault
     */
    public function getOutputResultsInfo($id, bool $includeDownloadUrl = false): bool|array
    {
        $Request = $this->RequestFabricator->Job_SSP()
            ->GetOutputResultsInfo()
            ->setInJobID($id);
        $Service = $this->ServiceFabricator->Job_SSP();
        $result = $Service->GetOutputResultsInfo($Request);

        $props = $result->getGetOutputResultsInfoResult();

        try {
            $count = $props->count();
        } catch (\Throwable $exception) {
            return false;
        }

        $propsCleaned = [];

        foreach ($props as $k => $prop) {
            if ($includeDownloadUrl) {
                $url = $this->getOutputResultDownloadURL($id, $k);
            } else {
                $url = null;
            }

            $propsCleaned[] = [
                'DownloadURL' => $url,
                'FilePath' => $prop->getM_FilePath(),
                'FileName' => $prop->getM_FileName(),
                'SizeKB' => $prop->getM_SizeKB(),
                'ModifDateStr' => $prop->getM_ModifDateStr(),
            ];
        }

        return $propsCleaned;
    }

    /**
     * @param $id
     * @return int
     * @throws SoapFault
     */
    public function getOutputCount($id): int
    {
        $Request = $this->RequestFabricator->Job_SSP()
            ->GetOutputResults()
            ->setInJobID($id);
        $Service = $this->ServiceFabricator->Job_SSP();
        $result = $Service->GetOutputResults($Request);

        return $result->getGetOutputResultsResult()->count();
    }

    /**
     * @param $id
     * @return array|null
     * @throws SoapFault
     */
    public function getMessagesDataSet($id): ?array
    {
        $Request = $this->RequestFabricator->Job_SSP()
            ->GetMessagesDataSet()
            ->setInJobID($id);
        $Service = $this->ServiceFabricator->Job_SSP();
        $xmlString = $Service->GetMessagesDataSet($Request)->getGetMessagesDataSetResult()->getAny();

        $a = Xml::toArray(Xml::build($xmlString));
        if (isset($a['diffgram']['NewDataSet']['Table'][0])) {
            $tables = $a['diffgram']['NewDataSet']['Table'];
        } else {
            if (isset($a['diffgram']['NewDataSet']['Table'])) {
                $tables = [$a['diffgram']['NewDataSet']['Table']];
            } else {
                $tables = [];
            }
        }

        return $tables;
    }


}