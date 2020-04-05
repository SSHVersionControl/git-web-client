<?php
// src/VersionControl/GitCommandBundle/GitCommands/GitSyncCommand.php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\GitCommands\Command;

use Exception;
use RuntimeException;
use VersionControl\GitCommandBundle\GitCommands\Exception\DeleteBranchException;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidBranchNameException;

/**
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitBranchCommand extends AbstractGitCommand
{
    /**
     * Get current active Branch Name
     * If there is no commits (eg new repo) then branch name is 'NEW REPO'
     * This git command needs at least one commit before if show the correct branch name.
     *
     * @return string The current branch name
     * @throws RunGitCommandException
     */
    public function getCurrentBranch(): string
    {
        try {
            $branchName = $this->command->runCommand($this->initGitCommand() . ' symbolic-ref --short -q HEAD');
        } catch (RunGitCommandException $e) {
            $branchName = $this->getCurrentBranchOldGit();
        }
        if (!$branchName) {
            return '(No Branch)';
        }

        return trim($branchName);
    }

    /**
     * Git "--short" does not work on older Git versions.
     *
     * @TODO Check for git version
     *
     * @return string
     * @throws RunGitCommandException
     */
    public function getCurrentBranchOldGit(): string
    {
        try {
            $response = $this->command->runCommand('git symbolic-ref HEAD');
            $tmp = explode('/', $response);
            return $tmp['2'];
        } catch (RunGitCommandException $e) {
            if ($this->getObjectCount() === 0) {
                return 'NEW REPO';
            }
        }

        return '(No Branch)';
    }

    /**
     * List all of the branches in your repository.
     * To list remote branches you may have to do a git fetch to
     * get latest changes.
     *
     * @param bool $local Flag to list local branches only
     *
     * @return array
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function getBranches($local = false): array
    {
        $command = 'git for-each-ref "--format=\'%(refname:short)\'"';
        if ($local === true) {
            $command .= ' ' . escapeshellarg('refs/heads/');
        }

        $localBranches = $this->command->runCommand($command);

        return $this->splitOnNewLine($localBranches);
    }

    /**
     * List all of the branches in your repository.
     * To list remote branches you may have to do a git fetch to
     * get latest changes.
     *
     * @return array
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function getRemoteBranches(): array
    {
        $command = 'git for-each-ref "--format=\'%(refname:short)\'"';

        $command .= ' ' . escapeshellarg('refs/remotes/');

        $localBranches = $this->command->runCommand($command);

        return $this->splitOnNewLine($localBranches);
    }

    /**
     * @return array
     * @throws RunGitCommandException
     */
    public function getBranchRemoteListing(): array
    {
        $gitBranches = array();

        $localBranches = $this->getBranches(true);

        $remoteBranches = $this->getRemoteBranches();

        foreach ($remoteBranches as $branchName) {
            $branchParts = explode('/', $branchName);
            $isLocal = in_array($branchParts[1], $localBranches, true);
            $gitBranches[] = array('name' => $branchName, 'local' => $isLocal);
        }

        return $gitBranches;
    }

    /**
     * Creates a new branch. It's important to understand that branches are just pointers to commits.
     * When you create a branch, all Git needs to do is create a new pointer—it does not change the
     * repository in any other way.
     *
     * @param string $branchName Name of new branch
     * @param bool $switchToBranch If true the new branch is checked out
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     * @throws InvalidBranchNameException
     */
    public function createLocalBranch($branchName, $switchToBranch = false): string
    {
        if (false === $this->validateBranchName($branchName)) {
            throw new InvalidBranchNameException('This is not a valid branch name');
        }

        $output = $this->command->runCommand(sprintf('git branch %s', escapeshellarg($branchName)));

        if ($switchToBranch) {
            $output .= $this->command->runCommand(
                sprintf('git checkout %s 2>&1', escapeshellarg($branchName))
            );

            //Trigger file alter Event
            $this->triggerGitAlterFilesEvent();
        }

        return $output;
    }

    /**
     * Creates a new branch from a remote branch. It's important to understand that branches are just pointers to
     * commits.
     * When you create a branch, all Git needs to do is create a new pointer—it does not change the
     * repository in any other way.
     *
     * @param string $branchName Name of new branch
     * @param $remoteBranchName
     * @param bool $switchToBranch If true the new branch is checked out
     *
     * @return string command response
     * @throws RuntimeException
     * @throws InvalidBranchNameException
     * @throws RunGitCommandException
     */
    public function createBranchFromRemote($branchName, $remoteBranchName, $switchToBranch = false): string
    {
        if ($this->validateBranchName($branchName)) {
            $output = $this->command->runCommand(
                sprintf('git branch %s %s 2>&1', escapeshellarg($branchName), escapeshellarg($remoteBranchName))
            );

            if ($switchToBranch) {
                $output .= $this->command->runCommand(sprintf('git checkout %s 2>&1', escapeshellarg($branchName)));
                //Trigger file alter Event
                $this->triggerGitAlterFilesEvent();
            }
        } else {
            throw new InvalidBranchNameException('This is not a valid branch name');
        }

        return $output;
    }

    /**
     * Validates Branch name. Checks if a branch name is allowed.
     *
     * @param string $branchName Name of new branch
     *
     * @return bool true if valid branch name
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function validateBranchName($branchName): bool
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            return true;
        }

        $this->command->runCommand(sprintf('git check-ref-format "refs/heads/%s"', $branchName), false);

        return !($this->command->getLastExitStatus() !== 0);
    }

    /**
     * Rename the current branch.
     *
     * @param string $branchName
     *
     * @return string command output
     *
     * @throws RunGitCommandException
     * @throws RuntimeException
     * @throws InvalidBranchNameException
     */
    public function renameCurrentBranch($branchName): string
    {
        if (false === $this->validateBranchName($branchName)) {
            throw new InvalidBranchNameException('This is not a valid branch name');
        }

        return $this->command->runCommand(sprintf('git branch -m "%s"', $branchName));
    }

    /**
     * The git checkout command lets you navigate between the branches created by git branch.
     * Checking out a branch updates the files in the working directory to match the version
     * stored in that branch, and it tells Git to record all new commits on that branch.
     *
     * @param string $branchName Name of new branch
     *
     * @return string command response
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function checkoutBranch($branchName): string
    {
        $response = $this->command->runCommand(sprintf('git checkout %s 2>&1', escapeshellarg($branchName)));

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }

    /**
     * Deletes Branch with branch name. Setting $forceDelete equals false is a “safe” operation in that Git prevents
     * you from
     * deleting the branch if it has unmerged changes.
     * Setting $forceDelete equals true force delete the specified branch, even if it has unmerged changes. This is
     * the command to use
     * if you want to permanently throw away all of the commits associated with a particular line of development.
     * Use with caution.
     *
     * @param string $branchName Name of branch to delete
     * @param bool $forceDelete Flag to delete branch, even if it has unmerged changes
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws DeleteBranchException
     * @throws RuntimeException
     */
    public function deleteBranch($branchName, $forceDelete = false): string
    {
        $currentBranch = $this->getCurrentBranch();
        if ($branchName === $currentBranch) {
            throw new DeleteBranchException(
                'You cannot delete the current branch. Please checkout a different branch before deleting.'
            );
        }
        if ($forceDelete === true) {
            $deleteFlag = '-D';
        } else {
            $deleteFlag = '-d';
        }

        return $this->command->runCommand(
            sprintf('git branch ' . $deleteFlag . ' %s 2>&1', escapeshellarg($branchName))
        );
    }

    /**
     * Merges current branch with branch of name
     * Merge the specified branch into the current branch, but always generate a merge commit (even if it was a
     * fast-forward merge). This is useful for documenting all merges that occur in your repository.
     *
     * @param string $branchName Name of branch to delete
     *
     * @return string command response
     * @throws RunGitCommandException
     */
    public function mergeBranch(string $branchName): string
    {
        $currentBranch = $this->getCurrentBranch();
        if ($branchName === $currentBranch) {
            throw new RuntimeException(
                'You cannot merge a branch with itself. Please checkout a '.
                'different branch before trying to merge.'
            );
        }
        $response = $this->command->runCommand(
            sprintf($this->initGitCommand() . ' merge --no-ff %s', escapeshellarg($branchName))
        );

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }

    /**
     * This attempts to reset your working copy to whatever state it was in before the merge. That means that it
     * should restore any uncommitted changes
     * from before the merge, although it cannot always do so reliably.
     * Generally you shouldn't merge with uncommitted changes anyway.
     *
     * Prior to version 1.7.4:
     *
     *   git reset --merge
     *   This is older syntax but does the same as the above.
     *
     *   Prior to version 1.6.2:
     *
     *   git reset --hard
     *   which removes all uncommitted changes, including the uncommitted merge. Sometimes this behaviour is useful
     *  even in newer versions of Git that support the above commands.
     *
     * @return string command response
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function abortMerge(): string
    {
        $response = $this->command->runCommand('git merge --abort 2>&1');

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }

    /**
     * List any files.
     *
     * @return array
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function listConflictedFiles(): array
    {
        return $this->splitOnNewLine($this->command->runCommand('git diff --name-only --diff-filter=U 2>&1'));
    }

    /**
     * Fetch all branches from all remote repositories.
     *
     * @return string command response
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function fetchAll(): string
    {
        return $this->command->runCommand('git fetch --all 2>&1');
    }
}
