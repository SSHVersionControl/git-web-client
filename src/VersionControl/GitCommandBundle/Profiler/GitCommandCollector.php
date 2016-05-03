<?php
// src/VersionControl/GitCommandBundle/Profiler/GitCommandCollector.php
/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitCommandBundle\Profiler;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use VersionControl\GitCommandBundle\Logger\GitCommandLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Profile for git commands. View git commands for a request in the Symfony2 dev profiler
 * 
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
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

