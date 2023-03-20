<?php

namespace App\XMPie\uProduce\Tasks;

use App\XMPie\uProduce\Clients\ClientFactory;

class JobManager extends BaseTasks
{

    public function __construct(array $xmpOptions, array $soapOptions, array $config)
    {
        parent::__construct($xmpOptions, $soapOptions, $config);

    }

    
}