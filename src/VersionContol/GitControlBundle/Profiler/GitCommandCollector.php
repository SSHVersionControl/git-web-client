<?php

// src/AppBundle/DataCollector/RequestCollector.php
namespace VersionContol\GitControlBundle\Profiler;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use VersionContol\GitControlBundle\Logger\GitCommandLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GitCommandCollector extends DataCollector
{
    /**
     * 
     * @var GitCommandLogger 
     */
    protected $logger;
    
    public function __construct(GitCommandLogger $logger)
    {
        $this->logger = $logger;
    }
    
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['commands_count'] = $this->logger->getCommandsCount();
        $this->data['commands'] = $this->logger->getCommands();
    }

    public function getCommands()
    {
        return $this->data['commands'];
    }

    public function getCommandsCount()
    {
        return $this->data['commands_count'];
    }
    
    public function getTime()
    {
        $time = 0;
        foreach ($this->data['commands'] as $query) {
            $time += $query['executionMS'];
        }

        return $time;
    }


    public function getName()
    {
        return 'version_control.gitcommand_collector';
    }
}

