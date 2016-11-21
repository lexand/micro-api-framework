<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 28.10.16
 * Time: 9:45
 */

namespace microapi\log;

use Monolog\Handler\HandlerInterface;

class Logger implements \Psr\Log\LoggerInterface {

    private static $defaultCfg = [
        'name'         => 'LOG',
        'enabled'      => true,
        'level'        => \Psr\Log\LogLevel::INFO,
        'handlerClass' => '\Monolog\Handler\StreamHandler',
        'handler.path' => null
    ];

    private $cfg = [];

    /**
     * @var \Monolog\Logger
     */
    private $log;

    /**
     * Logger constructor.
     * @param array $cfg
     */
    public function __construct(array $cfg) {
        $this->cfg = array_merge(static::$defaultCfg, $cfg);

        if ($this->cfg['enabled']) {
            $this->log = new \Monolog\Logger($this->cfg['name']);

            $this->log->pushHandler($this->getHandler($this->cfg));
        }
        else{
            $this->log = new \Psr\Log\NullLogger();
        }
    }

    public function emergency($message, array $context = []) {
        $this->log->emergency($message, $context);
    }

    public function alert($message, array $context = []) {
        $this->log->alert($message, $context);
    }

    public function critical($message, array $context = []) {
        $this->log->critical($message, $context);
    }

    public function error($message, array $context = []) {
        $this->log->error($message, $context);
    }

    public function warning($message, array $context = []) {
        $this->log->warning($message, $context);
    }

    public function notice($message, array $context = []) {
        $this->log->notice($message, $context);
    }

    public function info($message, array $context = []) {
        $this->log->info($message, $context);
    }

    public function debug($message, array $context = []) {
        $this->log->debug($message, $context);
    }

    public function log($level, $message, array $context = []) {
        $this->log->log($level, $message, $context);
    }

    private function getHandler(array $cfg) : HandlerInterface {
        switch ($cfg['handlerClass']) {
            case '\Monolog\Handler\StreamHandler':
                return new \Monolog\Handler\StreamHandler($cfg['handler.path'], $cfg['level']);
            default:
                throw new \LogicException('unsupported handler class');
        }
    }

}