<?php

namespace App\InputTriggers;

use App\XMPie\uProduce\Clients\ClientFactory;
use arajcany\ToolBox\Utility\TextFormatter;

class BaseTrigger
{
    protected string $classDirectory;
    protected ClientFactory $uProduceClientFactory;

    public function __construct()
    {
        $this->classDirectory = TextFormatter::makeDirectoryTrailingSmartSlash(dirname(__FILE__));
    }

    public function loadFactory($xmpOptions, $soapOptions, $config): void
    {
        $this->uProduceClientFactory = new ClientFactory($xmpOptions, $soapOptions, $config);
    }

    /**
     * @param $recipients
     * @param $variables
     * @param $adors
     * @return array
     */
    public function getTriggerMap($recipients, $variables, $adors): array
    {
        $map = [
            'Setup' => [
                ['name' => 'AccountID', 'value' => '', 'hint' => '[Optional] string or integer (The uProduce Account where the Campaign/Document exists. Will be inferred by the DocumentID if not provided.)'],
                ['name' => 'CampaignID', 'value' => '', 'hint' => '[Optional] string or integer (The uProduce Campaign where the Document exists. Will be inferred by the DocumentID if not provided.)'],
                ['name' => 'DocumentID', 'value' => '', 'hint' => '[Required] string or integer (The uProduce Document to produce.)'],
                ['name' => 'DestinationID', 'value' => '', 'hint' => '[Optional] string or integer (If provided will write to the specified DestinationID or UNC location.)'],
            ],
            'DataSource' => [
                ['name' => 'DataSourceID', 'value' => '', 'hint' => '[Optional] integer (If provided will be used as the Recipients)'],
                ['name' => 'DataSourceConnectionString', 'value' => '', 'hint' => '[Optional] string (If provided a new DataSource will be created and used as the Recipients)'],
                ['name' => 'DataSourceQuery', 'value' => '', 'hint' => '[Optional] string (If provided the SQL Statement will be used)'],
                ['name' => 'DataSourcePlanFilterName', 'value' => '', 'hint' => '[Optional] string (If provided the Plan Filter will be used)'],
                ['name' => 'DataSourceTableName', 'value' => '', 'hint' => '[Optional] string (If provided the table will be used)'],

            ],
            'Recipients' => [],
            'Variables' => [],
            'Adors' => [],
            'JobTicket' => [
                ['name' => 'OutputFileName', 'value' => '', 'hint' => '[Optional] string (If provided will overwrite the INDD file name.)'],
                ['name' => 'StartRecord', 'value' => '', 'hint' => '[Optional] integer (Leave blank to start at 1)'],
                ['name' => 'EndRecord', 'value' => '', 'hint' => '[Optional] integer (Leave blank to end at N)'],
                ['name' => 'JobType', 'value' => 'PRINT', 'hint' => '[Required] string (Must be either "PROOF" or "PRINT".)'],
                ['name' => 'JobPriority', 'value' => 'Normal', 'hint' => '[Optional] string (Must be one of "Lowest", "VeryLow", "Low", "Normal", "AboveNormal", "High", "VeryHigh", "Highest". Defaults to "Normal")'],
                ['name' => 'OutputType', 'value' => 'PDFO', 'hint' => '[Optional] string (Must be one of "PROOF_SET", "VPS", "PDF", "PDFO", "PDFVT1", "PPML", "VDX", "VIPP", "JPG", "PNG", "PS", "RECORD_SET". Defaults to "PDFO")'],
                ['name' => 'AutomaticSubSplit', 'value' => false, 'hint' => '[Optional] bool (true|false)'],
                ['name' => 'AutomaticMerge', 'value' => false, 'hint' => '[Optional] bool (true|false)'],
                ['name' => 'OutputBatchesOrRecords', 'value' => 'BATCHES', 'hint' => '[Optional] string (Must be one of "BATCHES", "RECORDS".)'],
                ['name' => 'OutputBatchesOrRecordsCount', 'value' => '1', 'hint' => '[Optional] integer (The number of BATCHES to produce or the number of RECORDS to put in a batch.)'],
                ['name' => 'OutputParameter_EMBEDED_ELEMENTS', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_EMBED_RESOURCES', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_EMBED_FONTS', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_CENTER_PAGE', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_ASSETS_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_FONTS_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_OVERFLOW_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_STYLE_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_BW_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_OUTPUT_SIZE_LIMIT_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_BLEED_TOP', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_BLEED_BOTTOM', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_BLEED_LEFTORINSIDE', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_BLEED_RIGHTOROUTSIDE', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_BLEED_USE_DOCUMENT_DEF', 'value' => true, 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_COPY_TYPE', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_COPY_NUM', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_PACK_ASSETS', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_PACK_RESOURCES', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_ASSET_STORAGE', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_RESOURCE_STORAGE', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_OUTPUT_RES', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_PDF_MULTI_SINGLE_RECORD', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_INTERACTIVE_PDF_ELEMENTS', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_VERSION', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_NATIVE_PDF_OPTIONS', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_OUTPUT_TYPE', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_EMBED_PPML_RESOURCES_IN_STREAM', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_ASSETS_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_BLEED_X', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_BLEED_Y', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_BLEED_USE_DOCUMENT_DEF', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_COPY_NUM', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_COPY_TYPE', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_DS_PER_DOC', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_EMBEDED_ELEMENTS', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_EMBED_FONTS', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_EMBED_PPML_RESOURCES_IN_STREAM', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_EMBED_RESOURCES', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_FONTS_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_LABEL_MASTER_PAGES', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_METADATA', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_NATIVE_PDF_OPTIONS', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_OUTPUT_VPC_STUB_PATH', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_OVERFLOW_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_PACK_ASSETS', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_PACK_RESOURCES', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_PDF_MULTI_SINGLE_RECORD', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_STYLE_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_USE_GLOBAL_CACHING', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_VERSION', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_OUTPUT_RES', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_FILE_NAME_ADOR', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_SEPARATE_UNIQUE_CONTENT', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_SEPARATE_REUSABLE_CONTENT', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_COPY_ASSETS_TO_REMOTE_DESTINATION', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_COPY_RESOURCES_TO_REMOTE_DESTINATION', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_IGNORE_FLATTENING_POLICY', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_USE_UPRODUCE_GLOBAL_CACHING', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_PACKAGE_COMPONENT_MASK', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_REQUIRE_INDESIGN_RENDERING', 'value' => '', 'hint' => '**Consult the API documentation'],
                ['name' => 'OutputParameter_USE_RIP_TRANSPARENCY', 'value' => '', 'hint' => '**Consult the API documentation'],

            ],

        ];

        foreach ($recipients as $recipient) {
            $map['Recipients'][] = [
                'name' => $recipient['name'],
                'value' => '',
                'hint' => $recipient['type'],
            ];

        }

        foreach ($variables as $variable) {
            $map['Variables'][] = [
                'name' => $variable['name'],
                'value' => null,
                'hint' => $variable['extended_type'],
            ];

        }

        foreach ($adors as $ador) {
            $map['Adors'][] = [
                'name' => $ador['name'],
                'value' => null,
                'hint' => $ador['extended_type'],
            ];

        }

        return $map;
    }

}