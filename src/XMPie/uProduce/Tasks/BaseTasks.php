<?php

namespace App\XMPie\uProduce\Tasks;

use App\XMPie\uProduce\Clients\ClientFactory;

class BaseTasks
{
    protected ClientFactory $ClientFactory;

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
    }

}