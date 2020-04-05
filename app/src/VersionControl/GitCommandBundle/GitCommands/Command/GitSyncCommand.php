<?php
// src/VersionControl/GitCommandBundle/GitCommands/Command/GitSyncCommand.php

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
 * Git uses a collaboration modal,which gives every developer their own copy of the repository, complete with its own
 * local history and branch structure. Users typically need to share a series of commits rather than a single changeset.
 * Instead of committing a changeset from a working copy to the central repository, Git lets you share entire branches
 * between repositories.
 *
 * The commands  below let you manage connections with other repositories,
 * publish local history by “pushing” branches to other repositories,
 * and see what others have contributed by “pulling” branches into your local repository.
 * Commands:
 *      remote
 *      fetch
 *      push
 *      pull
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitSyncCommand extends AbstractGitCommand
{
    protected $pullRebase = false;

    /**
     * List the remote connections you have to other repositories.
     *
     * $ git remote
     * origin
     *
     * @return array() Array of remote names
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function getRemotes(): array
    {
        return $this->splitOnNewLine($this->command->runCommand('git remote'));
    }

    /**
     * List the remote connections you have to other repositories with "URL"
     * $ git remote -v
     * origin    https://github.com/schacon/ticgit (fetch)
     * origin    https://github.com/schacon/ticgit (push)
     * pb    https://github.com/paulboone/ticgit (fetch)
     * pb    https://github.com/paulboone/ticgit (push).
     *
     * @return array eg (array(0 => "origin", 1 => "https://github.com/schacon/ticgit", 2 => "(push)")
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function getRemoteVersions(): array
    {

        $lines = $this->splitOnNewLine($this->command->runCommand('git remote -v'));
        $lineCount = count($lines);

        if ($lineCount <= 2) {
            return [];
        }

        $remotes = array();
        for ($i = 1; $i < $lineCount; $i += 2) {
            $parts = preg_split('/\s+/', $lines[$i]);
            if ($parts[2] === '(push)') {
                $remotes[] = $parts;
            }
        }

        return $remotes;
    }

    /**
     * Create a new connection to a remote repository. After adding a remote, you’ll be able to use
     * $remote as a convenient shortcut for $url in other Git commands.
     *
     * It’s generally not possible to push commits to a HTTP address.
     * For read-write access, you should use SSH instead.
     *
     * @param string $remote
     * @param string $url
     *
     * @return string
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function addRemote($remote, $url): string
    {
        $remotes = $this->command->runCommand(
            sprintf(
                'git remote add %s %s 2>&1',
                escapeshellarg($remote),
                escapeshellarg($url)
            )
        );

        return $remotes;
    }

    /**
     * Remove the connection to the remote repository called $remote.
     *
     * @param string $remote
     *
     * @return string
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function deleteRemote($remote): string
    {
        return $this->command->runCommand(sprintf('git remote rm %s 2>&1', escapeshellarg($remote)));
    }

    /**
     * Remove the connection to the remote repository called $remote.
     *
     * @param string $remote
     *
     * @param $newRemote
     *
     * @return string
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function renameRemote($remote, $newRemote): string
    {
        $remotes = $this->command->runCommand(
            sprintf(
                'git remote rename %s %s 2>&1',
                escapeshellarg($remote),
                escapeshellarg($newRemote)
            )
        );

        return $remotes;
    }

    /**
     * Fetch all of the branches from the repository.
     *
     * @param string $remote Name of remote Repository
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function fetchAll($remote): string
    {
        return $this->command->runCommand(sprintf('git fetch %s 2>&1', escapeshellarg($remote)));
    }

    /**
     * Fetch changes from the remote server.
     *
     * @param string $remote Name of remote Repository
     * @param string $branch Branch to fetch
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function fetch($remote, $branch): string
    {
        return $this->command->runCommand(
            sprintf(
                'git fetch %s %s 2>&1',
                escapeshellarg($remote),
                escapeshellarg($branch)
            )
        );
    }

    /**
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function resetPullRequest(): string
    {
        $response = $this->command->runCommand('git reset --hard ORIG_HEAD');

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }

    /**
     * Push specified branch to the remote repository.
     *
     * @param string $remote The remote server to push to eg origin
     * @param string $branch The branch to push to the remote server eg master
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function push($remote, $branch): string
    {
        $command = sprintf('git push %s %s', escapeshellarg(trim($remote)), escapeshellarg(trim($branch)));

        return $this->command->runCommand($command);
    }

    /**
     * Push all of your local branches to the specified remote repository.
     *
     * @param string $remote The remote server to push to eg origin
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function pushAll($remote): string
    {
        $command = sprintf('git push %s --all', escapeshellarg($remote));

        return $this->command->runCommand($command);
    }

    /**
     * Tags are not automatically pushed when you push a branch or use the --all option.
     * The --tags flag sends all of your local tags to the remote repository.
     *
     * @param string $remote The remote server to push to eg origin
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function pushTags($remote): string
    {
        $command = sprintf('git push %s --tags 2>&1', escapeshellarg($remote));

        return $this->command->runCommand($command);
    }

    /**
     * Pull changes to the remote repository.
     *
     * @param string $remote The remote server to push to eg origin
     * @param string $branch The branch to push to the remote server eg master
     *
     * @return string command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function pull($remote, $branch): string
    {
        $command = 'git pull';
        if ($this->pullRebase) {
            $command .= ' --rebase';
        }

        $command = sprintf($command . ' %s %s', escapeshellarg($remote), escapeshellarg($branch));

        $response = $this->command->runCommand($command);

        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();

        return $response;
    }

    /**
     * Gets the number of commits ahead and behind a remote branch.
     * Needs to call fetch first.
     *
     * This request should support caching
     *
     * @param string $branch local branch name
     *
     * @return array
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function commitCountWithRemote($branch): array
    {
        $remotes = $this->getRemotes();

        if (count($remotes) === 0) {
            return array('pushCount' => 0, 'pullCount' => 0);
        }

        $remoteBranch = $remotes[0] . '/' . $branch;
        try {
            $command = sprintf(
                'git rev-list --count --left-right %s...%s',
                escapeshellarg(trim($branch)),
                escapeshellarg(trim($remoteBranch))
            );
            $response = $this->command->runCommand($command);

            [$pushCount, $pullCount] = explode('	', $response);
            return array('pushCount' => trim($pushCount), 'pullCount' => trim($pullCount));
        } catch (RunGitCommandException $e) {
            //Remote branch does not exist. Do nothing
        }

        return array('pushCount' => 0, 'pullCount' => 0);
    }
}
