<?php
/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Tests\GitCommands\Command;

use VersionControl\GitCommandBundle\Tests\GitCommandTestCase;

/**
 * Description of GitBranchCommandTest.
 *
 * @author fr_user
 */
class GitCommitCommandTest extends GitCommandTestCase
{
    /**
     * setUp, called on every method.
     */
    public function setUp()
    {
        $this->initGitCommandsLocal();
        $this->gitCommands->command('init')->initRepository();
        $this->addFile('test');
        $this->gitCommands->command('commit')->stageAll();
        $this->gitCommands->command('commit')->commit('first commit', 'Paul Schweppe <paulschweppe@gmail.com>');
        $this->addFile('test2');
        $this->gitCommands->command('commit')->stageAll();
        $this->gitCommands->command('commit')->commit('Second commit', 'Paul Schweppe <paulschweppe@gmail.com>');
    }

    /**
     * Test Getting commit files.
     */
    public function testGetFilesToCommit()
    {
        $this->addFile('test3.txt');
        $this->updateFile('test2', 'Test content');

        $files = $this->gitCommands->command('commit')->getFilesToCommit();
        $this->assertCount(2, $files, 'Files to commit returns more to 2 enitities ');
        foreach ($files as $gitFile) {
            $this->assertInstanceOf("\VersionControl\GitCommandBundle\Entity\GitFile", $gitFile);
        }
        $gitFile1 = $files[0];
        $this->assertEquals('', $gitFile1->getFileType(), 'File type failed for git file 1');
        $this->assertEquals(' ', $gitFile1->getIndexStatus(), 'Index Status failed for git file 1');
        $this->assertEquals('test2', $gitFile1->getPath1(), 'Path 1 failed for git file 1');
        $this->assertEquals('M', $gitFile1->getWorkTreeStatus(), 'Work tree status failed for git file 1');
        $this->assertEquals('', $gitFile1->getPath2(), 'Path 2 failed for git file 1');

        $gitFile2 = $files[1];
        $this->assertEquals('', $gitFile2->getFileType(), 'File type failed for git file 2');
        $this->assertEquals('test3.txt', $gitFile2->getPath1(), 'Path 1 failed for git file 2');
        $this->assertEquals('?', $gitFile2->getIndexStatus(), 'Index Status failed for git file 2');
        $this->assertEquals('?', $gitFile2->getWorkTreeStatus(), 'Work tree status failed for git file 2');
        $this->assertEquals('', $gitFile2->getPath2(), 'Path 2 failed for git file 2');
    }
}
