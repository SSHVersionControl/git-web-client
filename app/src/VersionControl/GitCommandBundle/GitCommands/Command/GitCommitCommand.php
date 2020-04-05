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
use VersionControl\GitCommandBundle\Entity\GitFile;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;

/**
 * Description of GitFilesCommand.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitCommitCommand extends AbstractGitCommand
{
    /**
     * @var string
     */
    private $statusHash;

    /**
     * Stage files for commit.
     * In the short-format, the status of each path is shown as
     * XY PATH1 -> PATH2
     * where PATH1 is the path in the HEAD, and the ` -> PATH2` part is shown only when PATH1 corresponds to a
     * different path in the index/worktree (i.e. the file is renamed). The XY is a two-letter status code.
     *
     * The fields (including the ->) are separated from each other by a single space. If a filename contains whitespace
     * or other nonprintable characters, that field will be quoted in the manner of a C string literal: surrounded by
     * ASCII double quote (34) characters, and with interior special characters backslash-escaped.
     * For paths with merge conflicts, X and Y show the modification states of each side of the merge. For paths that
     * do not have merge conflicts, X shows the status of the index, and Y shows the status of the work tree. For
     * untracked paths, XY are ??. Other status codes can be interpreted as follows:
     * ' ' = unmodified
     * M = modified
     * A = added
     * D = deleted
     * R = renamed
     * C = copied
     * U = updated but unmerged
     *
     * Ignored files are not listed, unless --ignored option is in effect, in which case XY are !!.
     *
     * X          Y     Meaning
     * -------------------------------------------------
     * [MD]   not updated
     * M        [ MD]   updated in index
     * A        [ MD]   added to index
     * D         [ M]   deleted from index
     * R        [ MD]   renamed in index
     * C        [ MD]   copied in index
     * [MARC]           index and work tree matches
     * [ MARC]     M    work tree changed since index
     * [ MARC]     D    deleted in work tree
     * -------------------------------------------------
     * D           D    unmerged, both deleted
     * A           U    unmerged, added by us
     * U           D    unmerged, deleted by them
     * U           A    unmerged, added by them
     * D           U    unmerged, deleted by us
     * A           A    unmerged, both added
     * U           U    unmerged, both modified
     * -------------------------------------------------
     * ?           ?    untracked
     * !           !    ignored
     * -------------------------------------------------
     * If -b is used the short-format status is preceded by a line
     *
     * @TODO: No Support for copy yet
     *
     * @param array $files
     *
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function stageFiles(array $files): void
    {
        $gitFiles = $this->getFilesToCommit();

        //Validated that this status is same as previous
        $deleteFiles = array();
        $addFiles = array();

        $flippedFiles = array_flip($files);

        foreach ($gitFiles as $fileEntity) {
            if ($fileEntity->getWorkTreeStatus() === '!' || !isset($flippedFiles[$fileEntity->getPath1()])) {
                continue;
            }

            if ($fileEntity->getWorkTreeStatus() === 'D'
                && ($fileEntity->getIndexStatus() === ' '
                    || $fileEntity->getIndexStatus() === 'M'
                    || $fileEntity->getIndexStatus() === 'A')
            ) {
                //Delete files
                //[ MA]     D    deleted in work tree
                $deleteFiles[] = escapeshellarg($fileEntity->getPath1());
                continue;
            }

            if ($fileEntity->getIndexStatus() === 'R' && ($fileEntity->getWorkTreeStatus() === 'D')) {
                //Rename delete
                //[R]     D    deleted in work tree
                //$deleteFiles[] = escapeshellarg($fileEntity->getPath1());
                $deleteFiles[] = escapeshellarg($fileEntity->getPath2());
                continue;
            }

            if ($fileEntity->getIndexStatus() === 'R'
                && ($fileEntity->getWorkTreeStatus() === 'M'
                    || $fileEntity->getWorkTreeStatus() === 'A'
                    || $fileEntity->getWorkTreeStatus() === ' ')
            ) {
                //Rename ADD
                //[R]     [ M]
                //$deleteFiles[] = escapeshellarg($fileEntity->getPath1());
                $addFiles[] = escapeshellarg($fileEntity->getPath2());
                continue;
            }

            if ($fileEntity->getWorkTreeStatus() === ' ') {
                //[MARC]           index and work tree matches
                //Do Nothing
                continue;
            }

            $addFiles[] = escapeshellarg($fileEntity->getPath1());
        }

        //Run the commands once for add and delete
        if (count($deleteFiles) > 0) {
            $this->command->runCommand('git rm ' . implode(' ', $deleteFiles));
        }

        if (count($addFiles) > 0) {
            $this->command->runCommand('git add ' . implode(' ', $addFiles));
        }
    }

    /**
     * Stages the file to be committed.
     * Currently supports adding and removing file.
     *
     * @TODO Make it more efficent
     *
     * @param string $file path to file to commit
     *
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function stageFile($file): void
    {
        $this->stageFiles(array($file));
    }

    /**
     * Shortcut to stage all (new, modified, deleted) files.
     *
     * @return string Command response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function stageAll(): string
    {
        return $this->command->runCommand('git add -A');
    }

    /**
     * Commits any file that was been staged.
     *
     * @param string $message
     * @param string $author name<email>
     *
     * @return string response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function commit($message, $author): string
    {
        return $this->command->runCommand(
            $this->initGitCommand() . ' commit -m '
            . escapeshellarg($message) . ' --author=' . escapeshellarg($author)
        );
    }

    /**
     * Gets all files that need to be commited.
     *
     * @return array Array of GitFile objects
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function getFilesToCommit(): array
    {
        $statusData = $this->getStatus();
        $this->statusHash = hash('md5', $statusData);

        return $this->processStatus($statusData);
    }

    /**
     * Git status command
     * Response:
     *  D feedback.html
     *  ?? time-selectors/work.html.
     *
     * @return string Command Response
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function getStatus(): string
    {
        return $this->command->runCommand('git status -u --porcelain', true, false);
    }

    /**
     * Process the git status data into GitFile objects.
     *
     * @param string $statusData
     *
     * @return array Array of GitFile objects
     */
    protected function processStatus($statusData): array
    {
        $lines = $this->splitOnNewLine($statusData, false);

        if (!is_array($lines) || count($lines) === 0) {
            return [];
        }

        $files = [];
        foreach ($lines as $line) {
            if (trim($line)) {
                $files[] = new GitFile($line, $this->command->getGitPath());
            }
        }

        return $files;
    }

    /**
     * Get hash of git status.
     *
     * @return string hash
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function getStatusHash(): string
    {
        if (!$this->statusHash) {
            $stausData = $this->getStatus();
            $this->statusHash = hash('md5', $stausData);
        }

        return $this->statusHash;
    }

    /**
     * Counts the number of files not commited.
     *
     * @return int
     * @throws RuntimeException
     */
    public function countStatus(): int
    {
        $total = 0;
        $command = 'git status -u -s';

        try {
            $response = $this->command->runCommand($command);
            $lines = $this->splitOnNewLine($response, false);
            $total = count($lines);
        } catch (RunGitCommandException $e) {
            //continue
        }

        return $total;
    }
}
