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

use VersionControl\GitCommandBundle\GitCommands\GitDiffParser;
use VersionControl\GitCommandBundle\Entity\GitCommitFile;
use VersionControl\GitCommandBundle\Entity\Collections\GitCommitFileCollection;

/**
 * Description of GitDiffCommand.
 * 
 * From Git documentation (https://git-scm.com/docs/git-diff):
 * Show changes between the working tree and the index or a tree, changes between the index and a tree, changes between two trees, changes between two blob objects, or changes between two files on disk.
 * git diff [--options] [--] [<path>…​]
 * This form is to view the changes you made relative to the index (staging area for the next commit). In other words, the differences are what you could tell Git to further add to the index but you still haven’t. You can stage these changes by using git-add[1].
 * 
 * git diff --no-index [--options] [--] [<path>…​]
 * This form is to compare the given two paths on the filesystem. You can omit the --no-index option when running the command in a working tree controlled by Git and at least one of the paths points outside the working tree, or when running the command outside a working tree controlled by Git.
 * git diff [--options] --cached [<commit>] [--] [<path>…​]
 * This form is to view the changes you staged for the next commit relative to the named <commit>. Typically you would want comparison with the latest commit, so if you do not give <commit>, it defaults to HEAD. If HEAD does not exist (e.g. unborn branches) and <commit> is not given, it shows all staged changes. --staged is a synonym of --cached.
 *
 * git diff [--options] <commit> [--] [<path>…​]
 * This form is to view the changes you have in your working tree relative to the named <commit>. You can use HEAD to compare it with the latest commit, or a branch name to compare with the tip of a different branch.
 *
 * git diff [--options] <commit> <commit> [--] [<path>…​]
 * This is to view the changes between two arbitrary <commit>.
 *
 * git diff [--options] <commit>..<commit> [--] [<path>…​]
 * This is synonymous to the previous form. If <commit> on one side is omitted, it will have the same effect as using HEAD instead.
 *
 * git diff [--options] <commit>...<commit> [--] [<path>…​]
 * This form is to view the changes on the branch containing and up to the second <commit>, starting at a common ancestor of both <commit>. "git diff A...B" is equivalent to "git diff $(git-merge-base A B) B". You can omit any one of <commit>, which has the same effect as using HEAD instead.
 *
 * Just in case if you are doing something exotic, it should be noted that all of the <commit> in the above description, except in the last two forms that use ".." notations, can be any <tree>.
 *
 * For a more complete list of ways to spell <commit>, see "SPECIFYING REVISIONS" section in gitrevisions[7]. However, "diff" is about comparing two endpoints, not ranges, and the range notations ("<commit>..<commit>" and "<commit>...<commit>") do not mean a range as defined in the "SPECIFYING RANGES" section in gitrevisions[7].
 *
 * git diff [options] <blob> <blob>
 * This form is to view the differences between the raw contents of two blob objects.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitDiffCommand extends AbstractGitCommand
{

    /**
     * Get diff on a file, that has changes that are not committed.
     * git diff [--options] [--] [<path>…​]
     * This form is to view the changes you made relative to the index 
     * (staging area for the next commit). In other words, the differences 
     * are what you could tell Git to further add to the index but you still haven’t.
     *  
     * @return array()
     */
    public function getDiffFile($filename)
    {
        $diffString = $this->command->runCommand('git --no-pager diff  --oneline '.escapeshellarg($filename).'');
        $diffParser = new GitDiffParser($diffString);
        $diffs = $diffParser->parse();

        return $diffs;
    }

    /**
     * Get diff on a file between commits.
     *
     * @return array()
     */
    public function getDiffFileBetweenCommits($filename, $previousCommitHash, $commitHash)
    {
        $diffString = $this->command->runCommand('git --no-pager diff  --oneline '.escapeshellarg($previousCommitHash).' '.escapeshellarg($commitHash).' -- '.escapeshellarg($filename).'');
        $diffParser = new GitDiffParser($diffString);
        $diffs = $diffParser->parse();

        return $diffs;
    }

    /**
     * Returns a list of files effected by a commit.
     *
     * @return array() Array of VersionControl\GitCommandBundle\Entity\GitCommitFile
     */
    public function getFilesInCommit($commitHash)
    {
        $response = $this->command->runCommand('git diff-tree --no-commit-id --name-status -r '.escapeshellarg($commitHash).'');
        $responseLines = $this->splitOnNewLine($response);
        $files = new GitCommitFileCollection();
        foreach ($responseLines as $line) {
            $files->addGitCommitFile((new GitCommitFile($line)));
        }

        return $files;
    }

    /**
     * Gets the previous Commit Hash by from a commit has and related to a file
     * 
     * @param string $commitHash
     * @param string $fileName
     * @return string commit short hash
     */
    public function getPreviousCommitHash($commitHash = 'HEAD', $fileName = false)
    {
        $previousCommitHash = '';
        $command = " git log --pretty=format:'%h' -n 2 ".escapeshellarg($commitHash).'';
        if ($fileName !== false) {
            $command .= ' '.escapeshellarg($fileName);
        }
        $response = $this->command->runCommand($command);
        $responseLines = $this->splitOnNewLine($response);
        if (count($responseLines) == 2) {
            $previousCommitHash = trim($responseLines['1']);
        }

        return $previousCommitHash;
    }

}
