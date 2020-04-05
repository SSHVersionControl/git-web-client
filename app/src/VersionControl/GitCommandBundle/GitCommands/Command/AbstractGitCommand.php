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

use RuntimeException;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;
use VersionControl\GitCommandBundle\GitCommands\GitCommand;
use VersionControl\GitCommandBundle\GitCommands\GitEnvironmentInterface;
use VersionControl\GitCommandBundle\Event\GitAlterFilesEvent;
use VersionControl\GitControlBundle\Entity\User\User;

/**
 * Abstract Class for Git commands.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class AbstractGitCommand implements InterfaceGitCommand
{
    /**
     * @var GitCommand
     */
    protected $command;

    /**
     * AbstractGitCommand constructor.
     *
     * @param GitCommand $command
     */
    public function __construct(GitCommand $command)
    {
        $this->command = $command;
    }

    /**
     * @param $command
     *
     * @return string
     * @throws RunGitCommandException
     */
    public function runCommand($command): string
    {
        return $this->command->runCommand($command);
    }

    /**
     * Gets the number of objects in git repo
     * The command returns data in the format:
     *  3251 objects, 15308 kilobytes.
     *
     * @return int The number of objects
     * @throws RunGitCommandException
     */
    public function getObjectCount(): int
    {
        $result = $this->runCommand('git count-objects');
        $splits = explode(',', $result);
        //0 = object count 1 = size
        $objects = explode(' ', $splits[0]);

        return $objects[0];
    }

    /**
     * Splits a block of text on newlines and returns an array.
     *
     * @param string $text Text to split
     * @param bool $trimSpaces If true then each line is trimmed of white spaces. Default true
     *
     * @return array Array of lines
     */
    public function splitOnNewLine($text, $trimSpaces = true): array
    {
        if (!trim($text)) {
            return array();
        }
        $lines = preg_split('/$\R?^/m', $text);
        if ($trimSpaces) {
            return array_map(array($this, 'trimSpaces'), $lines);
        }

        return $lines;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function trimSpaces($value): string
    {
        return trim(trim($value), '\'');
    }

    /**
     * @param $eventName
     * @param $listener
     */
    public function addListener($eventName, $listener): void
    {
        $this->command->getEventDispatcher()->addListener($eventName, $listener);
    }

    /**
     * @param string $eventName
     */
    protected function triggerGitAlterFilesEvent($eventName = 'git.alter_files'): void
    {
        $event = new GitAlterFilesEvent($this->command->getGitEnvironment(), array());
        $this->triggerEvent($eventName, $event);
    }

    /**
     * @param $eventName
     * @param $event
     */
    protected function triggerEvent($eventName, $event): void
    {
        $this->command->dispatcher->dispatch($eventName, $event);
    }

    /**
     * Allows you to override the git Environment.
     *
     * @param GitEnvironmentInterface $gitEnvironment
     *
     * @return AbstractGitCommand
     */
    public function overRideGitEnvironment(GitEnvironmentInterface $gitEnvironment): AbstractGitCommand
    {
        $this->command->setGitEnvironment($gitEnvironment);

        return $this;
    }

    /**
     * @return string
     */
    public function initGitCommand(): string
    {
        $user = $this->command->getGitUser();

        return sprintf(
            'git -c user.email="%s" -c user.name="%s" -c core.quotepath=false -c log.showSignature=false',
            $user->getEmail(),
            $user->getUsername()
        );
    }
}
