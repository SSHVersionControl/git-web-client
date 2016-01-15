<?php

namespace VersionContol\GitControlBundle\Logger;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * Logger for the Elastica.
 *
 * The {@link logQuery()} method is configured as the logger callable in the
 * service container.
 *
 * @author Gordon Franke <info@nevalon.de>
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
     * Logs a query.
     *
     * @param string $path   Path to call
     * @param string $method Local or remote command
     * @param array  $data   arguments
     * @param float  $time   execution time
     */
    public function logCommand($command, $method, $data, $time)
    {
        if ($this->debug) {
            $this->commands[] = array(
                'command' => $command,
                'method' => $method,
                'data' => $data,
                'executionMS' => $time
            );
        }

        if (null !== $this->logger) {
            $message = sprintf("%s (%s) %0.2f ms", $command, $method, $time * 1000);
            $this->logger->info($message, (array) $data);
        }
    }

    /**
     * Returns the number of queries that have been logged.
     *
     * @return integer The number of queries logged
     */
    public function getCommandsCount()
    {
        return count($this->commands);
    }

    /**
     * Returns a human-readable array of queries logged.
     *
     * @return array An array of queries
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
