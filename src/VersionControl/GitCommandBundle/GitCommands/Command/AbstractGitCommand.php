<?php
/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\GitCommands\Command;

use VersionControl\GitCommandBundle\GitCommands\GitCommand;
use VersionControl\GitCommandBundle\GitCommands\GitEnvironmentInterface;
use VersionControl\GitCommandBundle\Event\GitAlterFilesEvent;

/**
 * Abstract Class for Git commands.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class AbstractGitCommand implements InterfaceGitCommand
{
    protected $command;

    public function __construct(GitCommand $command)
    {
        $this->command = $command;
    }

    public function runCommand($command)
    {
        return $this->command->runCommand($command);
    }

    /**
     * Gets the number of objects in git repo
     * The command returns data in the format:
     *  3251 objects, 15308 kilobytes.
     *
     * @return int The number of objects
     */
    public function getObjectCount()
    {
        $result = $this->runCommand('git count-objects');
        $splits = explode(',', $result);
        //0 = object count 1 = size
        $objects = explode(' ', $splits[0]);
        $objectCount = $objects[0];

        return $objectCount;
    }

    /**
     * Splits a block of text on newlines and returns an array.
     *
     * @param string $text       Text to split
     * @param bool   $trimSpaces If true then each line is trimmed of white spaces. Default true
     *
     * @return array Array of lines
     */
    public function splitOnNewLine($text, $trimSpaces = true)
    {
        if (!trim($text)) {
            return array();
        }
        $lines = preg_split('/$\R?^/m', $text);
        if ($trimSpaces) {
            return array_map(array($this, 'trimSpaces'), $lines);
        } else {
            return $lines;
        }
    }

    public function trimSpaces($value)
    {
        return trim(trim($value), '\'');
    }

    public function addListener($eventName, $listener)
    {
        $this->command->getEventDispatcher()->addListener($eventName, $listener);
    }

    protected function triggerGitAlterFilesEvent($eventName = 'git.alter_files')
    {
        $event = new GitAlterFilesEvent($this->command->getGitEnvironment(), array());
        $this->triggerEvent($eventName, $event);
    }

    protected function triggerEvent($eventName, $event)
    {
        $this->command->dispatcher->dispatch($eventName, $event);
    }

    /**
     * Allows you to override the git Environment.
     *
     * @param GitEnvironmentInterface $gitEnvironment
     *
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
     */
    public function overRideGitEnvironment(GitEnvironmentInterface $gitEnvironment)
    {
        $this->command->setGitEnvironment($gitEnvironment);

        return $this;
    }
}
