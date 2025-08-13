<?php

namespace App\XMPie\uProduce\Clients;

use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Utility\Inflector;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SoapFault;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\BasicServices\DataSourcePlanUtils_SSP\RecipientsInfo as DataSourceRecipientsInfo;

class DataSourceClient extends BaseClient
{
    public function __construct(array $xmpOptions = [], array $soapOptions = [], array $config = [])
    {
        parent::__construct($xmpOptions, $soapOptions, $config);
    }

    /**
     * @param $id
     * @return bool|null
     * @throws SoapFault
     */
    public function isExist($id): ?bool
    {
        $Request = $this->RequestFabricator->DataSource_SSP()
            ->IsExist()
            ->setInDataSourceID($id);
        $Service = $this->ServiceFabricator->DataSource_SSP();
        $result = $Service->IsExist($Request);

        return $result->getIsExistResult();
    }

    /**
     * Validate the DataSource by ID.
     * Will check that the current username/password can actually access the DataSource
     * Will return the DataSource ID or false
     *
     * You cannot validate a DataSource Name as DataSource Names are not unique
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
                if (isset($props['dataSourceID'])) {
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


    /**
     * Convert a simple array to a file
     *
     * @param $rawData
     * @param $planId
     * @param $saveLocation
     * @return bool
     * @throws SoapFault
     */
    public function convertRawDataToDataFileForPlan($rawData, $planId, $saveLocation): bool
    {
        $cleanData = $this->formatRawDataForWriting($rawData);
        if (!$cleanData) {
            return false;
        }

        //extract and inflect the headers
        $headers = array_keys($cleanData[0]);
        $headers = $this->inflectHeadersToPlanRecipientFields($headers, $planId);

        //ok to convert to csv
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('recipients');

            //write the header row
            $rowCounter = 1;
            $cellCounter = 1;
            foreach ($headers as $header) {
                $sheet->setCellValue([$cellCounter, $rowCounter], $header);
                $cellCounter++;
            }

            //write the data rows
            $rowCounter = 2;
            foreach ($cleanData as $row) {
                $cellCounter = 1;
                foreach ($row as $cell) {
                    $sheet->setCellValue([$cellCounter, $rowCounter], $cell);
                    $cellCounter++;
                }
                $rowCounter++;
            }

            $ext = pathinfo($saveLocation, PATHINFO_EXTENSION);

            if (in_array(strtolower($ext), ['csv', 'tsv', 'psv'])) {
                $writer = new Csv($spreadsheet);
                if (strtolower($ext) === 'csv') {
                    $writer->setDelimiter(',');
                } elseif (strtolower($ext) === 'tsv') {
                    $writer->setDelimiter("\t");
                } elseif (strtolower($ext) === 'psv') {
                    $writer->setDelimiter('|');
                }
                $writer->setEnclosure('"');
                $writer->save($saveLocation);
            } elseif (in_array(strtolower($ext), ['xls', 'xlsx'])) {
                $writer = new Xlsx($spreadsheet);
                $writer->save($saveLocation);
            }
        } catch (\Throwable $e) {
            return false;
        }

        if ($this->is_file_with_wait($saveLocation)) {
            return true;
        } else {
            return false;
        }

    }


    /**
     * Format unknown format raw data as a clean array ready for conversion to csv/xls
     *
     * @param mixed $rawData
     * @return array|false
     */
    public function formatRawDataForWriting(mixed $rawData): array|false
    {
        $data = false;
        $dataClean = [];

        //try converting an object
        if (is_object($rawData)) {
            try {
                $data = $rawData->toArray();
            } catch (\Throwable $exception) {
                return false;
            }
        }

        //try CSV decoding
        if (is_string($rawData)) {
            $rawDataTmp = trim($rawData);
            $rawDataTmp = str_replace("\r\n", "\n", $rawDataTmp);
            $rawDataTmp = str_replace("\r", "\n", $rawDataTmp);
            $lineCount = substr_count($rawDataTmp, "\n");

            $delimiters = [",", ";", ":", "|", "\t"];
            $highestDelimiterCount = 0;
            $highestDelimiter = false;
            foreach ($delimiters as $delimiter) {
                $currentCount = substr_count($rawData, $delimiter);
                if ($currentCount > $highestDelimiterCount) {
                    $highestDelimiterCount = $currentCount;
                    $highestDelimiter = $delimiter;
                }
            }

            if ($highestDelimiter && $lineCount >= 2 && $highestDelimiterCount >= $lineCount) {
                try {
                    $csv = Reader::createFromString($rawData);
                    $csv->setHeaderOffset(0);
                    $csv->setDelimiter($highestDelimiter);
                    $recs = $csv->getRecords();
                    $dataClean = [];
                    foreach ($recs as $rec) {
                        $dataClean[] = $rec;
                    }
                    return $dataClean;
                } catch (\Throwable $exception) {
                    return false;
                }
            }
        }

        //try JSON decoding
        if (is_string($rawData)) {
            $data = json_decode($rawData, JSON_OBJECT_AS_ARRAY);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data = false;
            }
        }

        //if it is already and array
        if (is_array($rawData)) {
            $data = $rawData;
        }

        //should be an array or false by now
        if (!is_array($data)) {
            return false;
        }

        /*
         * Formatting and return clean array (or false is problematic)
         */

        //make sure it has at least 1 row
        if (count($data) === 0) {
            return false;
        }

        //make sure it's multi-dimensional
        if (count($data) === count($data, COUNT_RECURSIVE)) {
            return false;
        }

        //make sure first level is numerically indexed
        if (!array_is_list($data)) {
            return false;
        }

        //make sure 1st row is an array
        if (!is_array($data[0])) {
            return false;
        }

        //make sure it's 'square'
        if ((count($data) * count($data[0])) != (count($data, COUNT_RECURSIVE) - count($data))) {
            return false;
        }

        //if first row is sequential array, try and use them as headers
        if (array_is_list($data[0])) {
            $headers = $data[0];
            foreach ($headers as $header) {
                if (is_numeric($header) || strlen($header) === 0) {
                    return false;
                }
            }
            unset($data[0]);
        } else {
            $headers = array_keys($data[0]);
            foreach ($headers as $header) {
                if (strlen($header) === 0) {
                    return false;
                }
            }
        }

        foreach ($data as $dataRow) {
            $dataClean[] = array_combine($headers, $dataRow);
        }

        return $dataClean;
    }

    /**
     * Inflect the given $fieldName to match the Plan recipient field names
     *
     * @param string|array $fieldNames
     * @param int $planId
     * @return array|string
     * @throws SoapFault
     */
    public function inflectHeadersToPlanRecipientFields(array|string $fieldNames, int $planId): array|string
    {
        if (is_string($fieldNames)) {
            $fieldNames = [$fieldNames];
            $format = 'string';
        } else {
            $format = 'array';
        }

        $PC = new PlanClient();
        $recipientFields = $PC->getRecipientFields($planId);
        $inflectedHeaders = $this->getInflections();

        foreach ($fieldNames as $headerKey => $fieldName) {
            foreach ($recipientFields as $recipientField) {
                foreach ($inflectedHeaders as $inflectedHeaderGroup) {
                    foreach ($inflectedHeaderGroup as $inflectedHeaderItem) {
                        if ($recipientField['name'] == $inflectedHeaderItem) {
                            if (in_array($fieldName, $inflectedHeaderGroup)) {
                                $fieldNames[$headerKey] = $inflectedHeaderItem;
                            }
                        }
                    }
                }
            }
        }

        if ($format == 'string') {
            return implode("", $fieldNames);
        } else {
            return $fieldNames;
        }
    }

    /**
     * The search and replace arrays for converting random column names to valid PlanRecipientFields names
     *
     * @return array
     */
    public function getInflections(): array
    {
        $inflections = [
            ['badge_id', 'badge_number', 'staff_number', 'staff_id', 'employee_number', 'employee_id'],
            ['first', 'first_name', 'f_name', 'fname', 'christian name', 'given_name', 'FirstName_VAR',],
            ['last', 'last_name', 'l_name', 'lname', 'surname', 'LastName_VAR',],
            ['middle', 'middle_name', 'm_name', 'mname', 'MiddleName_VAR',],
            ['address', 'address 1',],
            ['postcode', 'post code', 'zip', 'zip code', 'zipcode',],
            ['email', 'gmail', 'icloud', 'hotmail', 'email address',],
            ['company', 'company_name', 'business', 'business_name', 'organisation', 'organization', 'organisation_name', 'organization_name',],
            ['dob', 'd o b', 'd.o.b','date of birth', 'birthdate',  'birth date','birthday',  'birth day', 'born'],
        ];

        $inflectionsExpanded = [];

        foreach ($inflections as $k => $inflectionGroup) {
            $inflectionsExpanded[$k] = [];
            foreach ($inflectionGroup as $p => $inflectionItem) {
                $inflectionsExpanded[$k]['original' . "-$p"] = $inflectionItem;

                $inflectionItemDashed = Inflector::dasherize($inflectionItem);
                $inflectionItemSpaced = str_replace("-", " ", $inflectionItemDashed);
                $inflectionItemUnderscored = str_replace("-", "_", $inflectionItemDashed);

                $inflectionsExpanded[$k]['dash' . "-$p"] = $inflectionItemDashed;
                $inflectionsExpanded[$k]['underscore' . "-$p"] = $inflectionItemUnderscored;
                $inflectionsExpanded[$k]['camel' . "-$p"] = Inflector::camelize($inflectionItemSpaced);
                $inflectionsExpanded[$k]['variable' . "-$p"] = Inflector::variable($inflectionItemDashed);

                $inflectionsExpanded[$k]['lower' . "-$p"] = $inflectionItemSpaced;
                $inflectionsExpanded[$k]['upper' . "-$p"] = strtoupper($inflectionItemSpaced);
                $inflectionsExpanded[$k]['ucwords' . "-$p"] = Inflector::humanize($inflectionItemSpaced);
                $inflectionsExpanded[$k]['ucfirst' . "-$p"] = ucfirst(strtolower(Inflector::humanize($inflectionItemSpaced)));

                $inflectionsExpanded[$k]['dash-lower' . "-$p"] = str_replace(" ", "-", $inflectionsExpanded[$k]['lower' . "-$p"]);
                $inflectionsExpanded[$k]['dash-upper' . "-$p"] = str_replace(" ", "-", $inflectionsExpanded[$k]['upper' . "-$p"]);
                $inflectionsExpanded[$k]['dash-ucwords' . "-$p"] = str_replace(" ", "-", $inflectionsExpanded[$k]['ucwords' . "-$p"]);
                $inflectionsExpanded[$k]['dash-ucfirst' . "-$p"] = str_replace(" ", "-", $inflectionsExpanded[$k]['ucfirst' . "-$p"]);

                $inflectionsExpanded[$k]['underscore-lower' . "-$p"] = str_replace(" ", "_", $inflectionsExpanded[$k]['lower' . "-$p"]);
                $inflectionsExpanded[$k]['underscore-upper' . "-$p"] = str_replace(" ", "_", $inflectionsExpanded[$k]['upper' . "-$p"]);
                $inflectionsExpanded[$k]['underscore-ucwords' . "-$p"] = str_replace(" ", "_", $inflectionsExpanded[$k]['ucwords' . "-$p"]);
                $inflectionsExpanded[$k]['underscore-ucfirst' . "-$p"] = str_replace(" ", "_", $inflectionsExpanded[$k]['ucfirst' . "-$p"]);
            }

            $inflectionsExpanded[$k] = array_values(array_unique($inflectionsExpanded[$k]));
        }

        return $inflectionsExpanded;
    }


}