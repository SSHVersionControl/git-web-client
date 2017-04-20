<?php
// src/VersionControl/GitCommandBundle/GitCommands/GitTagCommand.php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\GitCommands\Command;

use VersionControl\GitCommandBundle\GitCommands\Exception\DeleteBranchException;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidBranchNameException;
use VersionControl\GitCommandBundle\Entity\GitTag;

/**
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitTagCommand extends AbstractGitCommand
{

    /**
     * List all of the Tags in your repository.
     *
     * @param bool $local Flag to list local branches only
     *
     * @return array | VersionControl\GitCommandBundle\Entity\GitTag
     */
    public function getTags()
    {
        $tagEntities = [];
        $command = "git for-each-ref --format '%(refname:short)|%(subject)|%(taggerdate)|%(taggername)|%(taggeremail)|%(*objectname)|%(*objectname:short)' refs/tags  --sort=taggerdate"; 

        $tags = $this->command->runCommand($command);
        $lines = $this->splitOnNewLine($tags, true);
        
        foreach ($lines as $line){
            $tagEntities[] = new GitTag($line); 
        }

        return $tagEntities;
    }


    /**
     * Creates a new Tag. 
     *
     * @param string $branchName     Name of new branch
     * @param bool   $switchToBranch If true the new branch is checked out
     *
     * @return string command response
     */
    public function createAnnotatedTag($version, $message, $commitShortCode = false )
    {
        if ($this->validateTagName($version)) {
            $command = sprintf('git tag -a %s -m %s', escapeshellarg($version), escapeshellarg($message));
            if($commitShortCode){
                 $command .= ' '.escapeshellarg($commitShortCode);
            }
            $output = $this->command->runCommand($command);
        } else {
            throw new InvalidBranchNameException('This is not a valid branch name');
        }

        return $output;
    }

    

    /**
     * Validates Tag name. Checks if a tag name is allowed.
     *
     * @param string $tagName Name of new branch
     *
     * @return bool true if valid branch name
     */
    public function validateTagName($tagName)
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            return true;
        } else {
            //$output = $this->command->runCommand(sprintf('(git check-ref-format "refs/heads/%s");echo -e "\n$?"',$branchName));
           $response = $this->command->runCommand(sprintf('git check-ref-format "refs/tags/%s"', $tagName), false);

            if ($this->command->getLastExitStatus() !== 0) {
                return false;
            }
        }

        return true;
    }
 
}
