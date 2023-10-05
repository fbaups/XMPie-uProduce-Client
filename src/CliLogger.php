<?php

namespace App;

use arajcany\ToolBox\Utility\TextFormatter;
use Cake\I18n\FrozenTime;
use League\CLImate\CLImate;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\StorageAttributes;

class CliLogger extends CLImate
{

    private null|string $currentTimestamp;
    private null|string $baseLogPath = null;

    public function __construct($baseLogPath = null)
    {
        parent::__construct();

        $this->currentTimestamp = date("Y-m-d-H-i-s");

        if ($baseLogPath) {
            $this->baseLogPath = $baseLogPath;
        } else {
            $this->baseLogPath = LOGS;
        }

        $this->purgeLogFiles();

        if (!is_dir($this->baseLogPath)) {
            @mkdir($this->baseLogPath, 0777, true);
        }

    }

    /**
     * Purge files older than the specified hours
     *
     * @param int $olderThanHours
     * @return void
     */
    public function purgeLogFiles(int $olderThanHours = 24): void
    {
        try {
            $adapter = new LocalFilesystemAdapter($this->baseLogPath);
            $filesystem = new Filesystem($adapter);

            $listing = $filesystem->listContents('');

            $deletionTime = (new FrozenTime())->subHours($olderThanHours);

            /** @var StorageAttributes $item */
            foreach ($listing as $item) {
                if ($item instanceof FileAttributes) {
                    $pathOriginals = $item->path();
                    $dateParts = str_replace([".log", ".txt"], "", $pathOriginals);
                    $dateParts = explode("-", pathinfo($dateParts, PATHINFO_FILENAME));
                    if (count($dateParts) !== 6) {
                        continue;
                    }

                    $timestamp = (new FrozenTime())
                        ->year($dateParts[0])
                        ->month($dateParts[1])
                        ->day($dateParts[2])
                        ->hour($dateParts[3])
                        ->minute($dateParts[4])
                        ->second($dateParts[5]);

                    if ($timestamp->lte($deletionTime)) {
                        unlink($this->baseLogPath . $pathOriginals);
                    }
                }
            }
        } catch (\Throwable $exception) {
        }
    }

    /**
     * @param string $currentTimestamp
     * @return CliLogger
     */
    public function setCurrentTimestamp(string $currentTimestamp): CliLogger
    {
        $this->currentTimestamp = $currentTimestamp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrentTimestamp(): ?string
    {
        return $this->currentTimestamp;
    }


    /**
     * @param string $baseLogPath
     * @return CliLogger
     */
    public function setBaseLogPath(string $baseLogPath): CliLogger
    {
        $baseLogPath = TextFormatter::makeDirectoryTrailingSmartSlash($baseLogPath);
        $this->baseLogPath = $baseLogPath;
        return $this;
    }

    public function lines($lineBreaks = 0)
    {
        if ($lineBreaks > 0) {
            foreach (range(1, $lineBreaks) as $breaks) {
                parent::out("");
            }
        }
    }

    /**
     * Debug and die
     *
     * @param string|array $str
     * @return void
     */
    public function dd($str)
    {
        $str = $this->captureAsString($str);
        parent::out($str);
        die();
    }

    /**
     * @param string|array $str
     * @param $lineBreaks
     * @return mixed|void
     */
    public function out($str, $lineBreaks = 0)
    {
        $str = $this->captureAsString($str);
        $this->logger($str, $lineBreaks);
        parent::out($str);
        $this->lines($lineBreaks);
    }

    public function red($str, $lineBreaks = 0)
    {
        $str = $this->captureAsString($str);
        $this->logger($str, $lineBreaks);
        parent::lightRed($str);
        $this->lines($lineBreaks);
    }

    public function green($str, $lineBreaks = 0)
    {
        $str = $this->captureAsString($str);
        $this->logger($str, $lineBreaks);
        parent::lightGreen($str);
        $this->lines($lineBreaks);
    }

    public function cyan($str, $lineBreaks = 0)
    {
        $str = $this->captureAsString($str);
        $this->logger($str, $lineBreaks);
        parent::lightCyan($str);
        $this->lines($lineBreaks);
    }

    public function yellow($str, $lineBreaks = 0)
    {
        $str = $this->captureAsString($str);
        $this->logger($str, $lineBreaks);
        parent::lightYellow($str);
        $this->lines($lineBreaks);
    }

    public function cmdOut($outArray, $returnValue)
    {
        if (isset($outArray[0]) && !isset($outArray[1])) {
            $outArray = $outArray[0];
        }

        if (isset($outArray[0]) && isset($outArray[1])) {
            if (empty($outArray[1])) {
                $outArray = $outArray[0];
            }
        }

        if (intval($returnValue) === 0) {
            if (!empty($outArray)) {
                $this->out($outArray);
            }

            if (empty($outArray)) {
                $this->out("Success flag raised but no success message received.");
            }
        } else {

            if (!empty($outArray)) {
                $this->red($outArray);
            }

            if (empty($outArray) && $returnValue > 0) {
                $this->red("Error flag raised but no error message received.");
            }

            if (!empty($returnValue)) {
                $this->red("Error Code: " . $returnValue);
            }
        }
    }

    public function resetLogFile()
    {
        $this->logger('', 1, false);
    }

    public function getLogFileLocation()
    {
        $ts = $this->currentTimestamp;
        $path = $this->baseLogPath . "{$ts}.log.txt";
        if (!is_file($path)) {
            file_put_contents($path, '');
        }
        return realpath($path);
    }

    private function logger($data, $lineBreaks = 1, $append = true)
    {
        $lineBreaks = max($lineBreaks, 1);

        $toLog = $this->captureAsString($data);

        foreach (range(1, $lineBreaks) as $breaks) {
            $toLog = $toLog . "\n";
        }

        $logFile = $this->getLogFileLocation();

        if ($append) {
            file_put_contents($logFile, $toLog, FILE_APPEND);
        } else {
            file_put_contents($logFile, $toLog);
        }
    }

    private function captureAsString($data)
    {
        ob_start();
        print_r($data);
        return ob_get_clean();
    }

    public function pauseSlider($pauseTime = 4, $label = '')
    {
        $progress = $this->progress()->total(100);
        for ($i = 0; $i <= 100; $i++) {
            $progress->current($i, $label);
            $pauser = (1000000 * $pauseTime) / 100;
            usleep($pauser);
        }
    }

    public function debugToFile(string $filename, mixed $data): bool|int
    {
        $logFile = $this->getLogFileLocation();
        $filename = pathinfo($logFile, PATHINFO_DIRNAME) . "/" . $filename;

        if (is_array($data) || is_object($data)) {
            $data = json_encode($data, JSON_PRETTY_PRINT);
        }

        return file_put_contents($filename, $data);
    }

}