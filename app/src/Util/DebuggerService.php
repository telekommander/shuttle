<?php

namespace App\Util;

/**
 * Class DebuggerService
 * @package App\Util
 */
class DebuggerService {

    private $debugbar = null;
    
    private $renderer = null;
    
    public function __construct($mode, $connection, $mailer, $logger)
    {
        if ($mode == 'dev') {
            $this->debugbar = new \DebugBar\StandardDebugBar();
            $this->renderer = $this->debugbar->getJavascriptRenderer();

            $tpdo = new \DebugBar\DataCollector\PDO\TraceablePDO($connection->getPdo());
            $connection->setPdo($tpdo);
            $this->debugbar->addCollector(new \DebugBar\DataCollector\PDO\PDOCollector($tpdo));
            
            $this->debugbar->addCollector(new \DebugBar\Bridge\MonologCollector($logger));
            
            $this->debugbar['messages']->aggregate(new \DebugBar\Bridge\SwiftMailer\SwiftLogCollector($mailer));
            $this->debugbar->addCollector(new \DebugBar\Bridge\SwiftMailer\SwiftMailCollector($mailer));
        }
    }

    public function addMessage($msg, $lvl = 'info')
    {
        if (isset($this->debugbar)) {
            $this->debugbar["messages"]->log($lvl, $msg);
        }
    }

    public function setBaseUrl($url)
    {
        if (isset($this->renderer)) {
            $this->renderer->setBaseUrl($url);
        }
    }

    public function renderCSS()
    {
        return isset($this->renderer) ? $this->renderer->dumpCssAssets() : "";
    }

    public function renderJS()
    {
        return isset($this->renderer) ? $this->renderer->dumpJsAssets() : "";
    }
    
    public function renderBar()
    {
        return isset($this->renderer)? $this->renderer->render(): '';
    }
}
