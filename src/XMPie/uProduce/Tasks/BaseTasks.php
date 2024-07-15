<?php

namespace App\XMPie\uProduce\Tasks;

use App\XMPie\uProduce\Clients\ClientFactory;
use arajcany\ToolBox\Utility\TextFormatter;

class BaseTasks
{
    protected ClientFactory $ClientFactory;
    protected string $tmpDir;

    public function __construct(array $xmpOptions, array $soapOptions, array $config)
    {
        $xmpOptionsDefault =
            [
                'url' => '',
                'admin_username' => '',
                'admin_password' => '',
                'username' => '',
                'password' => '',
            ];
        $xmpOptions = array_merge($xmpOptionsDefault, $xmpOptions);

        $soapOptionsDefault = [];
        $soapOptions = array_merge($soapOptionsDefault, $soapOptions);

        $configDefault = [
            'security' => true,
            'timezone' => 'utc',
        ];
        $configDefault = array_merge($configDefault, $config);


        $this->ClientFactory = new ClientFactory($xmpOptions, $soapOptions, $configDefault);

        $this->tmpDir = TextFormatter::makeDirectoryTrailingSmartSlash(sys_get_temp_dir());
        if (defined('TMP')) {
            $this->tmpDir = TMP;
        }

    }

}