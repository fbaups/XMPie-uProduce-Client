<?php

namespace App\Utility\Releases;

use arajcany\ToolBox\Utility\TextFormatter;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Progress;

/**
 *
 */
class TokenizerTasks
{
    private CLImate|false $io;
    private Progress|false $_progressBar = false;
    private bool $verbose = false;
    private array $cache = [];

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        try {
            $this->io = new CLImate;
        } catch (\Throwable $exception) {
            $this->io = false;
        }
    }

    /**
     * @param bool $verbose
     */
    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    private function applyReplacements($message, $replacers)
    {
        if (is_string($replacers) || is_int($replacers)) {
            $replacers = [$replacers];
        }

        if (!empty($replacers)) {
            foreach ($replacers as $k => $replacement) {
                $search = "{" . $k . "}";
                $message = str_replace($search, $replacement, $message);
            }
        }
        return $message;
    }

    private function out($message, ...$replacers)
    {
        if (!$this->verbose || !$this->io) {
            return;
        }
        $message = $this->applyReplacements($message, $replacers);
        $this->io->out($message);
    }

    private function info($message, ...$replacers)
    {
        if (!$this->verbose || !$this->io) {
            return;
        }
        $message = $this->applyReplacements($message, $replacers);
        $this->io->lightBlue($message);
    }

    private function success($message, ...$replacers)
    {
        if (!$this->verbose || !$this->io) {
            return;
        }
        $message = $this->applyReplacements($message, $replacers);
        $this->io->green($message);
    }

    private function warning($message, ...$replacers)
    {
        if (!$this->verbose || !$this->io) {
            return;
        }
        $message = $this->applyReplacements($message, $replacers);
        $this->io->lightYellow($message);
    }

    private function error($message, ...$replacers)
    {
        if (!$this->verbose || !$this->io) {
            return;
        }
        $message = $this->applyReplacements($message, $replacers);
        $this->io->lightRed($message);
    }

    private function progressBar($current, $total, $label = null)
    {
        if (!$this->verbose || !$this->io) {
            return;
        }

        $factor = ($total / 100);
        $currentFixed = intval(floor($current / $factor));
        $totalFixed = 100;
        if (empty($this->_progressBar)) {
            $this->_progressBar = $this->io->progress($totalFixed);
        }
        $this->_progressBar->total($totalFixed);
        $this->_progressBar->current($currentFixed, $label);
    }

    public function removeCommentsFromZipList($zipList, $tmpDir = null): array
    {
        if (empty($tmpDir)) {
            $subDir = substr(sha1(mt_rand()), 0, 8);
            $tmpDir = TMP . $subDir . DS;
            if (!is_dir($tmpDir)) {
                if (!@mkdir($tmpDir)) {
                    return $zipList;
                }
            }
        }

        $uncommentExtensions = ['php', 'ctp'];

        $totalCount = count($zipList);
        $everyNFiles = 13; //every N files

        foreach ($zipList as $k => $file) {
            $counter = $k + 1;
            if (($counter % $everyNFiles === 0) || $counter === $totalCount) {
                $message = $this->applyReplacements("Checking {0} of {1} files for comment stripping.", [$counter, $totalCount]);
                $this->progressBar($counter, $totalCount, $message);
            }

            if (is_array($file)) {
                if (isset($file['external']) && isset($file['internal'])) {
                    if (is_file($file['external'])) {
                        if (in_array(pathinfo($file['external'], PATHINFO_EXTENSION), $uncommentExtensions)) {
                            if (str_contains($file['external'], "vendor/") && !str_contains($file['external'], "vendor/arajcany/")) {
                                continue;
                            }

                            $contents = file_get_contents($file['external']);
                            $contents = $this->_removeBlockedCode($contents);
                            $contents = $this->_removeComments($contents);
                            $externalTmpLocation = $tmpDir . sha1($file['external']);
                            file_put_contents($externalTmpLocation, $contents);
                            $zipList[$k]['external'] = $externalTmpLocation;
                        }
                    }
                }
            }
        }

        return $zipList;
    }

    /**
     * Wrapper function.
     *
     * @param $fileOrString
     * @return string
     */
    public function removeComments($fileOrString): string
    {
        if (is_file($fileOrString)) {
            $contents = file_get_contents($fileOrString);
        } else {
            $contents = $fileOrString;
        }

        return $this->_removeComments($contents);
    }

    /**
     * Removes the comments whilst preserving the line count.
     * This helps with debugging as error line numbers in PROD will match DEV/UAT code.
     *
     * @param $strData
     * @return string
     */
    private function _removeComments($strData): string
    {
        $tokens = token_get_all($strData);
        $newText = '';

        foreach ($tokens as $token) {
            if (is_string($token)) {
                // simple 1-character token
                $newText .= $token;
            } else {
                // token array
                list($id, $text) = $token;

                switch ($id) {
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        $newText .= $this->replaceWithBlankLines($text);
                        break;

                    default:
                        // anything else -> output "as is"
                        $newText .= $text;
                        break;
                }
            }
        }

        return $newText;
    }

    /**
     * Removes code blocked from PROD whilst preserving the line count.
     * This helps with debugging as error line numbers in PROD will match DEV/UAT code.
     *
     * Start Tag =  '/* START_BLOCKED_CODE /'
     * End Tag =    '/* END_BLOCKED_CODE /'
     *
     * @param $strData
     * @return string
     */
    private function _removeBlockedCode($strData): string
    {
        $startTag = '/* START_BLOCKED_CODE */';
        $endTag = '/* END_BLOCKED_CODE */';

        return TextFormatter::stripBetweenTags($strData,$startTag,$endTag);
    }

    /**
     * Keeps the original number of lines
     *
     * @param $text
     * @return string
     */
    private function replaceWithBlankLines($text): string
    {
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);
        $count = substr_count($text, "\n");
        $newText = str_pad("", $count, "\n", STR_PAD_LEFT);
        return $newText;
    }

}
