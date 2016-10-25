<?php
// src/VersionControl/GitCommandBundle/GitCommands/Command/GitUndoCommand.php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\GitCommands\Command;

/**
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitUndoCommand extends AbstractGitCommand
{
    /**
     * @return string command response
     */
    public function resetPullRequest()
    {
        return $this->command->runCommand('git reset --hard ORIG_HEAD');
    }

    /**
     * Reverts commit but keeps the files unchanged.
     *
     * @return string command response
     */
    public function undoCommit()
    {
        return $this->command->runCommand('git reset --soft HEAD~1');
    }

    /**
     * @return string command response
     */
    public function undoCommitHard()
    {
        return $this->command->runCommand('git reset --hard HEAD~1');
    }

    /**
     * Update all files in the working directory to match the specified commit.
     * You can use either a commit hash or a tag as the $commitHash argument.
     * This will put you in a detached HEAD state.
     */
    public function checkoutCommit($commitHash = 'HEAD')
    {
        $response = $this->command->runCommand(sprintf('git checkout %s 2>&1', escapeshellarg($commitHash)));

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }

    /**
     * Check out a previous version of a file. This turns the <file> that resides in the working directory into an
     * exact copy of the one from <commit> and adds it to the staging area.
     *
     * @param string $file
     * @param string $commitHash
     *
     * @return string
     */
    public function checkoutFile($file, $commitHash = 'HEAD')
    {
        $response = $this->command->runCommand(sprintf('git checkout %s %s', escapeshellarg($commitHash), escapeshellarg($file)));

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }
}
