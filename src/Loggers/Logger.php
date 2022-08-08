<?php

namespace Norgul\Xmpp\Loggers;

use Exception;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    private string $logFolder;
    private string $logFile;

    public function __construct(?string $logFolder = null, ?string $logFile = null)
    {
        $this->logFolder = $logFolder ?? 'logs';
        $this->logFile = sprintf('%s/%s', $this->logFolder, $logFile ?? 'xmpp.log');

        $this->createLogDir();
    }

    protected function createLogDir(): void
    {
        if (!file_exists($this->logFolder)) {
            mkdir($this->logFolder, 0777, true);
        }
    }

    protected function writeToFile($file, $message)
    {
        try {
            file_put_contents($file, $message.PHP_EOL, FILE_APPEND);
        } catch (Exception $e) {
            // silent fail
        }
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $prefix = date('Y.m.d H:i:s').' '.($level ? " {$level}::" : ' ');
        $this->writeToFile($this->logFile, sprintf('%s %s %s', $prefix, $message, json_encode($context)));
    }
}
