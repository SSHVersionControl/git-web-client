<?php
/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Logger;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * Logger for the git commands.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitCommandLogger
{
    protected $logger;
    protected $commands;
    protected $debug;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger The Symfony logger
     */
    public function __construct(LoggerInterface $logger = null, $debug = false)
    {
        $this->logger = $logger;
        $this->commands = array();
        $this->debug = $debug;
    }

    /**
     * Logs a git command.
     *
     * @param string $command    Git command called
     * @param string $method     Local or remote command
     * @param array  $data       arguments
     * @param float  $time       execution time
     * @param string $response   execution time
     * @param string $error      execution time
     * @param int    $exitStatus execution time
     */
    public function logCommand($command, $method, $data, $time, $response = '', $error = '', $exitStatus = 0)
    {
        if ($this->debug) {
            $this->commands[] = array(
                'command' => $command,
                'method' => $method,
                'data' => $data,
                'executionMS' => $time,
                'response' => $response,
                'error' => $error,
                'exitStatus' => $exitStatus,
            );
        }

        if (null !== $this->logger) {
            $message = sprintf('%s (%s) %0.2f ms', $command, $method, $time * 1000);
            $this->logger->info($message, (array) $data);
        }
    }

    /**
     * Returns the number of git commands that have been logged.
     *
     * @return int The number of commands logged
     */
    public function getCommandsCount()
    {
        return count($this->commands);
    }

    /**
     * Returns a array of commands logged.
     *
     * @return array An array of git commands
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
