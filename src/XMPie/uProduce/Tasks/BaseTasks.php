<?php

namespace App\XMPie\uProduce\Tasks;

use App\XMPie\uProduce\Clients\ClientFactory;
use arajcany\ToolBox\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\TextFormatter;

class BaseTasks
{
    use ReturnAlerts;

    protected ClientFactory $ClientFactory;
    protected string $tmpDir;

    public function __construct(array $xmpOptions, array $soapOptions, array $config)
    {
        $this->ClientFactory = new ClientFactory($xmpOptions, $soapOptions, $config);

        $this->tmpDir = TextFormatter::makeDirectoryTrailingSmartSlash(sys_get_temp_dir());
        if (defined('TMP')) {
            $this->tmpDir = TMP;
        }

    }

}