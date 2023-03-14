<?php

namespace App\XMPie\uProduce;

use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use SoapFault;
use XMPieWsdlClient\XMPie\uProduce\v_12_0_1\BasicServices\DataSourcePlanUtils_SSP\RecipientsInfo as DataSourceRecipientsInfo;

class DataSourceClient extends BaseClient
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
        $Request = $this->RequestFabricator->DataSource_SSP()
            ->IsExist()
            ->setInDataSourceID($id);
        $Service = $this->ServiceFabricator->DataSource_SSP();
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
        $Request = $this->RequestFabricator->DataSource_SSP()
            ->GetName()
            ->setInDataSourceID($id);
        $Service = $this->ServiceFabricator->DataSource_SSP();
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
        $Request = $this->RequestFabricator->DataSource_SSP()
            ->GetID()
            ->setInDataSourceName($name)
            ->setInCampaignID($campaignId);
        $Service = $this->ServiceFabricator->DataSource_SSP();
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
        $Request = $this->RequestFabricator->DataSource_SSP()
            ->GetAllProperties()
            ->setInDataSourceID($id);
        $Service = $this->ServiceFabricator->DataSource_SSP();
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
        $Request = $this->RequestFabricator->DataSource_SSP()
            ->Delete()
            ->setInDataSourceID($id);
        $Service = $this->ServiceFabricator->DataSource_SSP();
        $result = $Service->Delete($Request);

        return $result->getDeleteResult();
    }

    /**
     * Create a Counter DataSource.
     * Wrapper function, formats the $options for the Generic DS creation method
     *
     * @param array $options
     * @return bool|int|string
     * @throws SoapFault
     */
    public function createCounterDS(array $options = []): bool|int|string
    {
        $tz = Configure::read('XMPieClient.config.timezone');
        $currentDate = (new Chronos())->setTimezone($tz);
        $date = $currentDate->format("Y-m-d H:i:s");

        $defaultOptions = [
            'campaignId' => null,
            'name' => "{$date} - Counter DS",
            'from' => 1,
            'to' => 1,
            'step' => 1,
            'fieldName' => 'Index',
            'deleteSource' => false,
        ];

        //merge defaults
        $options = array_merge($defaultOptions, $options);

        //force some defaults
        $options['type'] = 'CNT';
        $options['additionalInfo'] = '';
        $options['sourceFolder'] = '';
        $options['props'] = [];

        //make $options ready for the Generic method
        $connectionString = $options['from'] . "," . $options['to'] . "," . $options['step'] . "," . $options['fieldName'];
        $options['connectionString'] = $connectionString;

        //unset any leftovers
        unset($options['from'], $options['to'], $options['step'], $options['fieldName']);

        //create via the Generic method
        return $this->createDataSource($options);
    }

    /**
     * Create a TXT DataSource.
     * Wrapper function, formats the $options for the Generic DS creation method
     *
     * @param $data
     * @param array $options
     * @return bool|int|string
     * @throws SoapFault
     */
    public function createTxtDS($data, array $options = []): bool|int|string
    {
        $tz = Configure::read('XMPieClient.config.timezone');
        $currentDate = (new Chronos())->setTimezone($tz);
        $date = $currentDate->format("Y-m-d H:i:s");
        $rnd = sha1(mt_rand() . mt_rand());
        $rndShort = substr($rnd, 0, 8);

        $defaultOptions = [
            'campaignId' => null,
            'fileName' => '',
            'delimiter' => '',
            'deleteSource' => false,
        ];

        //merge defaults
        $options = array_merge($defaultOptions, $options);

        //upload the data to TmpStorage
        $TS = new TempStorageClient();
        $extension = pathinfo($options['fileName'], PATHINFO_EXTENSION);
        $tmpFileName = "{$rnd}.{$extension}";
        $storageToken = $TS->uploadToStorageFileInFolder($tmpFileName, $data);

        //force some defaults
        $options['type'] = 'TXT';
        $options['name'] = "{$date} [{$rndShort}] - {$options['fileName']}";
        $options['connectionString'] = '';

        //make $options ready for the Generic method
        /*
        The @,@ is required to signify the delimiter in the TXT file
        e.g. @,@, means that file is comma (,) delimited
        e.g. @,@; means that file is semicolon (;) delimited
        e.g. @,@: means that file is colon (:) delimited
        e.g. @,@| means that file is pipe (|) delimited
        */
        $delimiterSyntax = "@,@{$options['delimiter']}";
        $options['additionalInfo'] = $options['fileName'] . $delimiterSyntax;
        $options['sourceFolder'] = $storageToken['folder_token'];
        $options['props'] = [
            'dataSourceFileName' => $tmpFileName,
        ];

        //unset any leftovers
        unset($options['folderName'], $options['fileName'], $options['delimiter']);

        //create via the Generic method
        $dataSourceId = $this->createDataSource($options);

        //delete the TmpStorage
        $TS->deleteTmpStorageFolder($storageToken['folder_token']);

        return $dataSourceId;
    }

    /**
     * Create a XLSX DataSource.
     * Wrapper function, formats the $options for the Generic DS creation method
     *
     * @param $data
     * @param array $options
     * @return bool|int|string
     * @throws SoapFault
     */
    public function createXlsxDS($data, array $options = []): bool|int|string
    {
        $tz = Configure::read('XMPieClient.config.timezone');
        $currentDate = (new Chronos())->setTimezone($tz);
        $date = $currentDate->format("Y-m-d H:i:s");
        $rnd = sha1(mt_rand() . mt_rand());
        $rndShort = substr($rnd, 0, 8);

        $defaultOptions = [
            'campaignId' => null,
            'fileName' => '',
            'delimiter' => '',
            'deleteSource' => false,
        ];

        //merge defaults
        $options = array_merge($defaultOptions, $options);

        //upload the data to TmpStorage
        $TS = new TempStorageClient();
        $extension = pathinfo($options['fileName'], PATHINFO_EXTENSION);
        $tmpFileName = "{$rnd}.{$extension}";
        $storageToken = $TS->uploadToStorageFileInFolder($tmpFileName, $data);

        //force some defaults
        $options['type'] = 'XLS';
        $options['name'] = "{$date} [{$rndShort}] - {$options['fileName']}";
        $options['connectionString'] = '';
        $options['additionalInfo'] = '';

        //make $options ready for the Generic method
        $options['additionalInfo'] = $options['fileName'];
        $options['sourceFolder'] = $storageToken['folder_token'];
        $options['props'] = [
            'dataSourceFileName' => $tmpFileName,
        ];

        //unset any leftovers
        unset($options['folderName'], $options['fileName'], $options['delimiter']);

        //create via the Generic method
        $dataSourceId = $this->createDataSource($options);

        //delete the TmpStorage
        $TS->deleteTmpStorageFolder($storageToken['folder_token']);

        return $dataSourceId;
    }


    /**
     * Generic Method to create a DataSource
     *
     * @param array $options
     * @return bool|int|string
     * @throws SoapFault
     */
    private function createDataSource(array $options = []): bool|int|string
    {
        $tz = Configure::read('XMPieClient.config.timezone');
        $currentDate = (new Chronos())->setTimezone($tz);
        $date = $currentDate->format("Y-m-d H:i:s");

        $defaultOptions = [
            'campaignId' => null,
            'type' => null,
            'name' => "{$date} - DataSource",
            'connectionString' => '',
            'additionalInfo ' => '',
            'sourceFolder' => '',
            'deleteSource' => false,
            'props' => [],
        ];

        $o = array_merge($defaultOptions, $options);

        //must be set
        if (is_null($o['campaignId'])) {
            return false;
        }

        //must be set
        if (is_null($o['type'])) {
            return false;
        }

        $Request = $this->RequestFabricator->DataSource_SSP()->CreateNew()
            ->setInCampaignID($o['campaignId'])
            ->setInType($o['type'])
            ->setInName($o['name'])
            ->setInConnectionString($o['connectionString'])
            ->setInAdditionalInfo($o['additionalInfo'])
            ->setInSourceFolder($o['sourceFolder'])
            ->setInDeleteSource($o['deleteSource'])
            ->setInProps($o['props']);
        $Response = $this->ServiceFabricator->DataSource_SSP()->CreateNew($Request);
        $dataSourceId = $Response->getCreateNewResult();

        if (is_numeric($dataSourceId)) {
            $dataSourceId = intval($dataSourceId);
        }

        return $dataSourceId;
    }

    /**
     * Get the DataSource Count
     * You can specify the TableName or the first compatible table will be used
     *
     * @param $dataSourceId
     * @param null $tableName
     * @return int
     * @throws SoapFault
     */
    public function getDataSourceCount($dataSourceId, $tableName = null): int
    {
        $Request = $this->RequestFabricator->DataSource_SSP()->GetPlan()->setInDataSourceID($dataSourceId);
        $Response = $this->ServiceFabricator->DataSource_SSP()->GetPlan($Request);
        $planId = $Response->getGetPlanResult();

        $compatibleTables = $this->getDataSourceCompatibleTables($dataSourceId);

        if (!$tableName) {
            if (isset($compatibleTables[0])) {
                $tableName = $compatibleTables[0];
            } else {
                return 0;
            }
        }

        $ri = (new DataSourceRecipientsInfo())
            ->setM_From(-1)//static value leave as -1
            ->setM_To(-1)//static value leave as -1
            ->setM_Filter($tableName)//name of the table in the SQL Database
            ->setM_FilterType(3)
            ->setM_SubFilter('')
            ->setM_recipientIDListFileName('')
            ->setM_recipientIDListMergeType('');

        $Request = $this->RequestFabricator->DataSourcePlanUtils_SSP()->GetRecipientsCount()->setInPlanID($planId)->setInDataSourceID($dataSourceId)->setInRIInfo($ri);
        $Response = $this->ServiceFabricator->DataSourcePlanUtils_SSP()->GetRecipientsCount($Request);
        return $Response->getGetRecipientsCountResult();
    }

    /**
     * Get all compatible tables from the DataSource
     *
     * @param $dataSourceId
     * @return array
     * @throws SoapFault
     */
    public function getDataSourceCompatibleTables($dataSourceId): array
    {
        $Request = $this->RequestFabricator->DataSource_SSP()->GetPlan()->setInDataSourceID($dataSourceId);
        $Response = $this->ServiceFabricator->DataSource_SSP()->GetPlan($Request);
        $planId = $Response->getGetPlanResult();

        $Request = $this->RequestFabricator->DataSourcePlanUtils_SSP()->GetCompatibleTables()->setInPlanID($planId)->setInDataSourceID($dataSourceId)->setInTrivialPlan(false);
        $Response = $this->ServiceFabricator->DataSourcePlanUtils_SSP()->GetCompatibleTables($Request);
        $tables = $Response->getGetCompatibleTablesResult();

        $tablesAsArray = [];

        foreach ($tables as $k => $table) {
            $tablesAsArray[] = $table;
        }

        return $tablesAsArray;
    }


}