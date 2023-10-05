<?php

namespace App\Utility\Feedback;

trait ReturnAlerts
{
    private array $successAlerts = [];
    private array $dangerAlerts = [];
    private array $warningAlerts = [];
    private array $infoAlerts = [];

    //often when running in CLI, a single return value and message are needed.
    private int $returnValue = 0;
    private string $returnMessage = '';


    /**
     * Ultra-fine micro time
     *
     * @return float
     */
    private function getMicrotime()
    {
        $mt = explode(' ', microtime());
        return $mt[1] . "." . substr(explode(".", $mt[0])[1], 0, 6);
    }


    /**
     * @param int $returnValue
     */
    public function setReturnValue(int $returnValue)
    {
        $this->returnValue = $returnValue;
    }

    /**
     * @return int
     */
    public function getReturnValue(): int
    {
        return $this->returnValue;
    }

    /**
     * @param string $returnMessage
     */
    public function setReturnMessage(string $returnMessage): void
    {
        $this->returnMessage = $returnMessage;
    }

    /**
     * @return string
     */
    public function getReturnMessage(): string
    {
        return $this->returnMessage;
    }

    /**
     * Return Alerts in their base array format.
     *
     * NOTE: this delivers the alerts out of sequence - they are grouped by level.
     *
     * @return array
     */
    public function getAllAlerts(): array
    {
        return [
            'success' => array_values($this->successAlerts),
            'danger' => array_values($this->dangerAlerts),
            'warning' => array_values($this->warningAlerts),
            'info' => array_values($this->infoAlerts),
        ];
    }

    /**
     * Return Alerts ready for a mass into a log style table.
     *
     * @param string $levelFieldName
     * @param string $messageFieldName
     * @return array
     */
    public function getAllAlertsForMassInsert(string $levelFieldName = 'level', string $messageFieldName = 'message'): array
    {
        $compiled = [];

        foreach ($this->successAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", intval($timestamp)) . $ms,
                $levelFieldName => 'success',
                $messageFieldName => $message,
            ];
        }

        foreach ($this->dangerAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", intval($timestamp)) . $ms,
                $levelFieldName => 'danger',
                $messageFieldName => $message,
            ];
        }

        foreach ($this->warningAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", intval($timestamp)) . $ms,
                $levelFieldName => 'warning',
                $messageFieldName => $message,
            ];
        }

        foreach ($this->infoAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", intval($timestamp)) . $ms,
                $levelFieldName => 'info',
                $messageFieldName => $message,
            ];
        }

        ksort($compiled);
        return $compiled;
    }

    /**
     * Return alerts in more like a standard log file format.
     * Still an array where every entry needs to be written as a line to file.
     *
     * @return array
     */
    public function getAllAlertsLogSequence(): array
    {
        $compiled = [];

        foreach ($this->successAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = date("Y-m-d H:i:s", intval($timestamp)) . ".{$ms} SUCCESS: {$message}";
        }

        foreach ($this->dangerAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = date("Y-m-d H:i:s", intval($timestamp)) . ".{$ms} DANGER: {$message}";
        }

        foreach ($this->warningAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = date("Y-m-d H:i:s", intval($timestamp)) . ".{$ms} WARNING: {$message}";
        }

        foreach ($this->infoAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = date("Y-m-d H:i:s", intval($timestamp)) . ".{$ms} INFO: {$message}";
        }

        ksort($compiled);
        return array_values($compiled);
    }

    /**
     * @return array
     */
    public function getSuccessAlerts(): array
    {
        return $this->successAlerts;
    }

    /**
     * @return array
     */
    public function getDangerAlerts(): array
    {
        return $this->dangerAlerts;
    }

    /**
     * @return array
     */
    public function getWarningAlerts(): array
    {
        return $this->warningAlerts;
    }

    /**
     * @return array
     */
    public function getInfoAlerts(): array
    {
        return $this->infoAlerts;
    }

    /**
     * @param array|string $message
     * @return array
     */
    public function addSuccessAlerts(array|string $message): array
    {
        return $this->_addAlert($message, 'successAlerts');
    }

    /**
     * @param array|string $message
     * @return array
     */
    public function addDangerAlerts(array|string $message): array
    {
        return $this->_addAlert($message, 'dangerAlerts');
    }

    /**
     * @param array|string $message
     * @return array
     */
    public function addWarningAlerts(array|string $message): array
    {
        return $this->_addAlert($message, 'warningAlerts');
    }

    /**
     * @param array|string $message
     * @return array
     */
    public function addInfoAlerts(array|string $message): array
    {
        return $this->_addAlert($message, 'infoAlerts');
    }

    /**
     * Try to add the right alert type based on the error string
     *
     * @param array|string $message
     * @return array
     */
    public function addSmartAlerts(array|string $message): array
    {
        if (is_string($message)) {
            $message = [$message];
        }

        foreach ($message as $item) {
            if (str_contains(strtolower($item), 'error')) {
                $this->addDangerAlerts($item);
            } elseif (str_contains(strtolower($item), 'warning')) {
                $this->addWarningAlerts(__($item));
            } elseif (str_contains(strtolower($item), 'danger')) {
                $this->addDangerAlerts($item);
            } elseif (str_contains(strtolower($item), 'success')) {
                $this->addSuccessAlerts($item);
            } else {
                $this->addInfoAlerts(__($item));
            }
        }

        return $this->getAllAlerts();
    }

    /**
     * Set an alert with micro-timestamp as the key.
     *
     * @param array|string $messages
     * @param string $type
     * @return array
     */
    private function _addAlert(array|string $messages, string $type): array
    {
        if (is_string($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            $this->$type[$this->getMicrotime()] = $message;
        }

        return $this->$type;
    }
}
