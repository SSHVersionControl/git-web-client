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

use VersionControl\GitCommandBundle\GitCommands\Exception\DeleteBranchException;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;
use VersionControl\GitCommandBundle\Tests\GitCommandTestCase;

/**
 * Description of GitBranchCommandTest.
 *
 * @author fr_user
 */
class GitBranchCommandTest extends GitCommandTestCase
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
     * Test current branch.
     */
    public function testGetCurrentBranch()
    {
        $currentBranch = $this->gitCommands->command('branch')->getCurrentBranch();
        $this->assertEquals('master', $currentBranch, 'master does not equal ' . $currentBranch);
    }

    /**
     * Test create local branch.
     */
    public function testCreateLocalBranch()
    {
        $branchCommand = $this->gitCommands->command('branch');

        $this->assertEquals('', $branchCommand->createLocalBranch('branch1'), 'create branch command');
        $this->assertEquals("Switched to branch 'branch2'", $branchCommand->createLocalBranch('branch2', true),
            'create branch command and switch');

        $errorMessage = '';
        try {
            $branchCommand->createLocalBranch('branch1');
        } catch (RunGitCommandException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals("fatal: A branch named 'branch1' already exists.", trim($errorMessage),
            'create branch command error');
    }

    /**
     * Test Get branches.
     */
    public function testGetBranches()
    {
        $branchCommand = $this->gitCommands->command('branch');
        $branches = $branchCommand->getBranches();
        $this->assertEquals(array('master'), $branches, ' branch listing does not equal expected');
        $branchCommand->createLocalBranch('branch1');
        $this->assertEquals(array('branch1', 'master'), $branchCommand->getBranches(),
            ' branch listing does not equal expected');

        $this->assertEquals(array('branch1', 'master'), $branchCommand->getBranches(true),
            ' branch listing does not equal expected');
    }

    /**
     * Test Get branches.
     */
    public function testGetRemoteBranches()
    {
        $branchCommand = $this->gitCommands->command('branch');
        $branches = $branchCommand->getRemoteBranches();
        $this->assertEquals(array(), $branches, ' branch listing does not equal expected');
    }

    public function testGetBranchRemoteListing()
    {
        $branchCommand = $this->gitCommands->command('branch');
        $remoteBranches = $branchCommand->getBranchRemoteListing();
        $this->assertEquals(array(), $remoteBranches, ' branch listing does not equal expected');
    }

    public function testRenameCurrentBranch()
    {
        $branchCommand = $this->gitCommands->command('branch');
        $response = $branchCommand->renameCurrentBranch('branch1');
        $this->assertEquals('', $response, 'Rename master to branch1 does not work');
        $currentBranch = $branchCommand->getCurrentBranch();
        $this->assertEquals('branch1', $currentBranch, 'Rename master to branch1 does not work');
    }

    public function testCheckoutBranch()
    {
        $branchCommand = $this->gitCommands->command('branch');
        $branchCommand->createLocalBranch('branch1');

        $response = $branchCommand->checkoutBranch('branch1');
        $this->assertEquals("Switched to branch 'branch1'", $response, 'Checkout branch1 does not work');

        $currentBranch = $branchCommand->getCurrentBranch();
        $this->assertEquals('branch1', $currentBranch, 'Checkout branch1 not correct');
    }

    /**
     * Test branch names.
     */
    public function testValidateBranchName()
    {
        $this->assertTrue($this->gitCommands->command('branch')->validateBranchName('test'),
            'Branch name "test" should be valid');
        $this->assertTrue($this->gitCommands->command('branch')->validateBranchName('test1232'),
            'Branch name "test" should be valid');
        $this->assertTrue($this->gitCommands->command('branch')->validateBranchName('test!£$test'),
            'Branch name "test!£$test" should be valid');

        $this->assertFalse($this->gitCommands->command('branch')->validateBranchName('..test'),
            'Branch name "..test" should be invalid');
        $this->assertFalse($this->gitCommands->command('branch')->validateBranchName('te:st'),
            'Branch name "te:st" should be invalid');
        $this->assertFalse($this->gitCommands->command('branch')->validateBranchName('te~st'),
            'Branch name "te~st" should be invalid');
        $this->assertFalse($this->gitCommands->command('branch')->validateBranchName('te^st'),
            'Branch name "te^st" should be invalid');
        $this->assertFalse($this->gitCommands->command('branch')->validateBranchName('test/'),
            'Branch name "test/" should be invalid');
        $this->assertFalse($this->gitCommands->command('branch')->validateBranchName('\test'),
            'Branch name "\test" should be invalid');
        $this->assertFalse($this->gitCommands->command('branch')->validateBranchName('test test'),
            'Branch name "test test" should be invalid');
    }

    public function testDeleteBranch()
    {
        $branchCommand = $this->gitCommands->command('branch');
        $branchCommand->createLocalBranch('branch1');
        $response = $branchCommand->deleteBranch('branch1');
        $this->assertContains('Deleted branch branch1', $response, 'Delete branch1 does not work');

        $errorMessage = '';
        try {
            $branchCommand->deleteBranch('master');
        } catch (DeleteBranchException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals('You cannot delete the current branch. Please checkout a different branch before deleting.',
            trim($errorMessage), 'create branch command error');
    }

    public function testMergeBranch()
    {
        $branchCommand = $this->gitCommands->command('branch');

        $branchCommand->createLocalBranch('branch1', true);


        $this->addFile('test3');
        $this->gitCommands->command('commit')->stageAll();
        $this->gitCommands->command('commit')->commit('Third commit', 'Paul Schweppe <paulschweppe@gmail.com>');

        $branchCommand->checkoutBranch('master');

        $response = $branchCommand->mergeBranch('branch1');

        $this->assertContains('1 file changed, 1 insertion(+)', $response, 'Merge branch failed');
    }
}
