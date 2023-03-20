<?php

namespace App\Test\XMPie\uProduce;

use App\XMPie\uProduce\Clients\ClientFactory;
use App\XMPie\uProduce\Clients\CustomerClient;
use PHPUnit\Framework\TestCase;

class CustomerClientTest extends TestCase
{
    private ClientFactory $ClientFactory;

    public function __construct(string $name)
    {
        parent::__construct($name);

        if (!is_file(__DIR__ . "/configuration.json")) {
            $xmpOptions =
                [
                    'url' => '',
                    'admin_username' => '',
                    'admin_password' => '',
                    'username' => '',
                    'password' => '',
                ];

            $soapOptions = [];

            $config = [
                'security' => true,
                'timezone' => 'utc',
            ];

            $contents = [
                'xmpOptions' => $xmpOptions,
                'soapOptions' => $soapOptions,
                'config' => $config,
            ];
            file_put_contents(__DIR__ . "/configuration.json", json_encode($contents, JSON_PRETTY_PRINT));
        }

        $configuration = file_get_contents(__DIR__ . "/configuration.json");
        $configuration = json_decode($configuration, JSON_OBJECT_AS_ARRAY);


        $this->ClientFactory = new ClientFactory($configuration['xmpOptions'], $configuration ['soapOptions'], $configuration['config']);

    }

    public function testCustomerClient()
    {
        $result = $this->ClientFactory->CustomerClient()->getAllProperties();

        $actual = array_keys($result);
        $expected = [
            "customerID",
            "customerGUID",
            "customerName",
            "customerStatus",
            "customerCreated",
            "statusReason",
            "adminID",
            "appearance",
            "accountsQuota",
            "campaignsQuota",
            "documentsQuota",
            "printQuota",
            "emailQuota",
            "onDemandQuota",
            "proofLimit",
            "proofSetLimit",
            "isDeleted",
            "loginName",
        ];

        $this->assertEquals($expected, $actual);
    }
}
