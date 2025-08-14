<?php

namespace App\XMPie\uProduce\Clients;

use arajcany\ToolBox\Utility\Feedback\ReturnAlerts;
use Cake\Core\Configure;
use XMPieWsdlClient\uProduceFactory;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\Fabricator\RequestFabricator;
use XMPieWsdlClient\XMPie\uProduce\v_13_2\Fabricator\ServiceFabricator;

class BaseClient
{
    use ReturnAlerts;

    protected array $xmpOptions;
    protected array $soapOptions;
    protected array $config;

    protected RequestFabricator $RequestFabricator;
    protected ServiceFabricator $ServiceFabricator;

    public function __construct(array $xmpOptions = [], array $soapOptions = [], array $config = [])
    {
        $this->xmpOptions = array_merge($this->getDefaultXmpOptions(), $xmpOptions);
        $this->soapOptions = array_merge($this->getDefaultSoapOptions(), $soapOptions);
        $this->config = array_merge($this->getDefaultConfigOptions(), $config);

        $Factory = new uProduceFactory($this->xmpOptions, $this->soapOptions, $this->config);
        $this->RequestFabricator = $Factory->getUProduceRequestFabricator();
        $this->ServiceFabricator = $Factory->getUProduceServiceFabricator();

        $this->polyfillFunctions();
    }

    /**
     * Polyfill functions for < PHP 8.1
     */
    private function polyfillFunctions(): void
    {
        if (!function_exists("array_is_list")) {
            function array_is_list(array $array): bool
            {
                $i = -1;
                foreach ($array as $k => $v) {
                    ++$i;
                    if ($k !== $i) {
                        return false;
                    }
                }
                return true;
            }
        }
    }

    /**
     * Extension of is_file() but with a wait time to give FS as chance to complete operations.
     *
     * @param string $filename
     * @param int $waitForSeconds how long to wait up to in secs
     * @return bool
     */
    public function is_file_with_wait(string $filename, int $waitForSeconds = 1): bool
    {
        $waitForSeconds = min($waitForSeconds, 10);//hard limit of 10 seconds

        $cycles = 10;
        $tries = range(1, $cycles);
        $waitForSeconds = ($waitForSeconds * 1000000) / $cycles;

        foreach ($tries as $try) {
            $result = is_file($filename);
            if ($result) {
                return true;
            } else {
                usleep($waitForSeconds);
            }
        }

        return false;
    }

    public function getDefaultXmpOptions(): array
    {
        return [
            'url' => '',
            'admin_username' => '',
            'admin_password' => '',
            'username' => '',
            'password' => '',
        ];
    }

    public function getDefaultSoapOptions(): array
    {
        return [];
    }

    public function getDefaultConfigOptions(): array
    {
        return [
            'security' => true,
            'timezone' => 'utc',
        ];
    }
}