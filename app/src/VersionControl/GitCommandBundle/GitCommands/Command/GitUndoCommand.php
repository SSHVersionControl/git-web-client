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

use RuntimeException;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;

/**
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitUndoCommand extends AbstractGitCommand
{
    /**
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function resetPullRequest(): string
    {
        return $this->command->runCommand('git reset --hard ORIG_HEAD');
    }

    /**
     * Reverts commit but keeps the files unchanged.
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function undoCommit(): string
    {
        return $this->command->runCommand('git reset --soft HEAD~1');
    }

    /**
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function undoCommitHard(): string
    {
        return $this->command->runCommand('git reset --hard HEAD~1');
    }

    /**
     * Update all files in the working directory to match the specified commit.
     * You can use either a commit hash or a tag as the $commitHash argument.
     * This will put you in a detached HEAD state.
     *
     * @param string $commitHash
     *
     * @return string
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function checkoutCommit($commitHash = 'HEAD'): string
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
     * @param bool $triggerGitAlterFilesEvent
     *
     * @return string
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function checkoutFile($file, $commitHash = 'HEAD', $triggerGitAlterFilesEvent = true): string
    {
        $response = $this->command->runCommand(
            sprintf('git checkout %s %s', escapeshellarg($commitHash), escapeshellarg($file))
        );

        //Trigger file alter Event
        if ($triggerGitAlterFilesEvent === true) {
            $this->triggerGitAlterFilesEvent();
        }

        return $response;
    }

    /**
     * Check out a file from merge
     *
     * @param string $file
     *
     * @return string
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function checkoutTheirFile($file): string
    {
        $this->command->runCommand(sprintf('git checkout --theirs %s', escapeshellarg($file)));
        $this->command->runCommand(sprintf('git add %s', escapeshellarg($file)));
        $response = 'Using their merged in file for "' . $file . '"';

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }

    /**
     * Check out a file from merge conflict
     *
     * @param string $file
     *
     * @return string
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function checkoutOurFile($file): string
    {
        $this->command->runCommand(sprintf('git checkout --ours %s', escapeshellarg($file)));
        $this->command->runCommand(sprintf('git add %s', escapeshellarg($file)));
        $response = 'Using original file from current branch for "' . $file . '"';

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }

    /**
     * Check out a file from merge conflict
     *
     * @param string $file
     *
     * @return string
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function addFile($file): string
    {
        $this->command->runCommand(sprintf('git add %s', escapeshellarg($file)));
        $response = 'Manually fixed file "' . $file . '"';

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }

    /**
     * Check out a file from merge conflict
     *
     * @param string $file
     *
     * @return string
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function deleteFile($file): string
    {
        $this->command->runCommand(sprintf('git rm %s', escapeshellarg($file)));
        $response = 'Delete file "' . $file . '"';

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }
}
