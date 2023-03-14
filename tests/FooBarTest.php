<?php

namespace App\Test;

use App\FooBar;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class FooBarTest extends TestCase
{

    public function testReturnTrue()
    {
        $FooBar = new FooBar();
        $result = $FooBar->returnTrue();
        assertEquals(true, $result);
    }

    public function testReturnFalse()
    {
        $FooBar = new FooBar();
        $result = $FooBar->returnFalse();
        assertEquals(false, $result);
    }
}
