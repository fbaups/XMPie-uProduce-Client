<?php

namespace App\Utility\Install;


class InstallerTasks
{
    public static function preInstall()
    {
       echo "preInstall\r\n";
    }

    public static function postInstall()
    {
        echo "postInstall\r\n";

    }

    public static function postPackageInstall()
    {
        echo "postPackageInstall\r\n";
    }
}
