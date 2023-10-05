<?php


namespace App\Utility\Releases;

use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\ZipPackager;
use League\CLImate\CLImate;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToWriteFile;

/**
 * Class BuildTasks
 *
 * Builds a release ZIP file.
 *
 * @property string $buildFile
 * @property CLImate $io
 *
 * @package App\Utility\Release
 */
class BuildTasks
{
    use ReturnAlerts;

    private array $log = [];
    private ?CLImate $io = null;
    private string $appName;

    /**
     * DefaultApplication constructor.
     *
     * @param null $buildFile
     */
    public function __construct()
    {
        $this->io = new CLImate();
        $this->setAppName('SampleApp');
    }

    /**
     * @param mixed $io
     */
    public function setIo(CLImate $io)
    {
        $this->io = $io;
    }

    /**
     * @param string $appName
     */
    public function setAppName(string $appName): void
    {
        $this->appName = str_replace(" ", "_", $appName);
    }


    /**
     * Write to the log variable
     *
     * @param $data
     * @param string $ioOutput
     */
    public function writeToLog($data, string $ioOutput = 'out'): void
    {
        if (is_object($data)) {
            $data = json_decode(json_encode($data));
        }

        if ($ioOutput === 'red') {
            $this->addDangerAlerts($data);
        } elseif ($ioOutput === 'yellow') {
            $this->addWarningAlerts($data);
        } elseif ($ioOutput === 'green') {
            $this->addSuccessAlerts($data);
        } elseif ($ioOutput === 'blue') {
            $this->addInfoAlerts($data);
        } else {
            $this->addInfoAlerts($data);
        }

        if (PHP_SAPI === 'cli') {
            if ($this->io) {
                $this->io->$ioOutput($data);
            }
        }
    }

    /**
     * Builds the release ZIP file according to the parameters specified in $this->buildFile
     *
     * @return bool
     */
    public function build(): bool
    {
        //check connection to the Remote Update Server
        $RemoteUpdateServer = new RemoteUpdateServer();

        if (empty($RemoteUpdateServer->remote_update_url)) {
            $this->writeToLog(__('Empty value for the Remote Update URL. Have you configured the CONFIG/remote_update.json file?'), 'yellow');
        }

        $remoteFilesystem = $RemoteUpdateServer->getRemoteUpdateServer();
        if (!$remoteFilesystem) {
            $this->writeToLog(__('Remote Update Server Unavailable. I will not be able to upload this package for people to upgrade.'), 'red');
        }

        $app_name = $this->appName;

        $date = date("Y-m-d--H-i-s");
        $zipFullPath = ROOT . DS . "../{$date}_{$app_name}.zip";
        $zipFileName = pathinfo($zipFullPath, PATHINFO_BASENAME);
        $baseDir = ROOT . DS;

        $this->writeToLog("Creating ZIP file {$zipFullPath}...");

        $rejectFilesFolders = [
            ".idea/",
            ".git/",
            ".gitattributes",
            ".gitignore",
            ".phpunit.result.cache",
            "config/cacert.pem",
            "config/config_database.php",
            "config/config_mail.php",
            "config/remote_update.json",
            "bin/PackageBuilder.bat",
            "bin/PackageBuilder.php",
            "composer.json",
            "composer.lock",
            "logs/",
            "package.php",
            "phpunit.xml",
        ];

        $otherFileNamesInVendor = [
            "Dockerfile",
            "docs.Dockerfile",
            "CREDITS",
            "composer.json",
            "composer.lock",
            "psalm.xml",
            "phpstan.neon.dist",
            ".editorconfig",
            ".pullapprove.yml",
            ".gitignore",
        ];

        $zp = new ZipPackager();
        $rawFileList = $zp->rawFileList($baseDir);
        $rawFileList = $zp->filterOutVendorExtras($rawFileList);
        $rawFileList = $zp->filterOutByFileName($rawFileList, $otherFileNamesInVendor);
        $rawFileList = $zp->filterOutFoldersAndFiles($rawFileList, $rejectFilesFolders);

        //convert raw file list to zip list
        $zipList = $zp->convertRawFileListToZipList($rawFileList, $baseDir, $app_name);

        //----strip comments------------------------------------------------------
        $TokenizerTasks = new TokenizerTasks();
        $tmpDir = TMP . "_" . mt_rand(11111, 99999) . "_tokenizer/";
        @mkdir($tmpDir, 0777, true);
        $zipList = $TokenizerTasks->removeCommentsFromZipList($zipList, $tmpDir);
        //------------------------------------------------------------------------

        //give some info
        $this->writeToLog(__('Zipping {0} files, this could take a while...', count($zipList)), 'blue');

        //make the zip
        $result = $zp->makeZipFromZipList($zipFullPath, $zipList);

        //----remove tokenized files----------------------------------------------
        $adapter = new LocalFilesystemAdapter($tmpDir);
        $tmpFileSystem = new Filesystem($adapter);
        $path = '';
        try {
            $tmpFileSystem->deleteDirectory($path);
            $this->writeToLog(__('Deleted TMP dir {0}', $tmpDir));
        } catch (\Throwable $exception) {
            $this->writeToLog(__('Unable to delete TMP dir {0}', $tmpDir), 'red');
        }
        //------------------------------------------------------------------------

        //----automatic upload to remote update site---------------------------------
        if ($remoteFilesystem) {
            $this->writeToLog(__('Uploading {0} to the Remote Update Server.', $zipFileName));
            try {
                $remoteFilesystem->write($zipFileName, file_get_contents($zipFullPath));
                $isWritten = true;
                $this->writeToLog("Zip file successfully uploaded to the Remote Update Server.", 'green');
            } catch (FilesystemException|UnableToWriteFile $exception) {
                $isWritten = false;
                $this->writeToLog("Could not upload the Zip file to the Remote Update Server.", 'red');
            }
        } else {
            $this->writeToLog(__("No automatic uploading of Zip to the Remote Update Server."), 'yellow');
        }
        //------------------------------------------------------------------------

        if ($result) {
            $this->writeToLog("Created ZIP... Exiting!", 'green');
        } else {
            $this->writeToLog("Failed to created ZIP... Exiting!", 'red');
        }

        return $result;
    }

    /**
     * Last resort function to fix Vendor code known issues
     */
    function applyCodeReplacements(): void
    {
        $file = ROOT . "\\some\\file\\example.php";
        if (is_file($file)) {
            $contents = file_get_contents($file);
            $contents = str_replace('input', 'output', $contents);
            file_put_contents($file, $contents);
        }

    }

}
