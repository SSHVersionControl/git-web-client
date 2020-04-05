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

use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;
use VersionControl\GitCommandBundle\Tests\GitCommandTestCase;

/**
 * Description of GitBranchCommandTest.
 *
 * @author fr_user
 */
class GitTagCommandTest extends GitCommandTestCase
{
    /**
     * setUp, called on every method.
     */
    public function setUp(): void
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
    public function testGetTagsNoTags()
    {
        $tags = $this->gitCommands->command('tag')->getTags();
        $this->assertCount(0, $tags, 'Tag count does not equal expected 0. Actual:' . count($tags));
    }

    /**
     * Test create local branch.
     */
    public function testCreateAnnotatedTag()
    {
        $tagCommand = $this->gitCommands->command('tag');

        $this->assertEquals('', $tagCommand->createAnnotatedTag('tag1', 'Test of tag1'), 'create tag command');

        $tags = $this->gitCommands->command('tag')->getTags();
        $this->assertCount(1, $tags, 'Tag count does not equal expected 1. Actual:' . count($tags));

        $errorMessage = '';
        try {
            $tagCommand->createAnnotatedTag('tag1', 'Test of tag1');
        } catch (RunGitCommandException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals("fatal: tag 'tag1' already exists", trim($errorMessage), 'create branch command error');
    }

    public function testPushTag()
    {
        $tagCommand = $this->gitCommands->command('tag');

        $this->assertEquals('', $tagCommand->createAnnotatedTag('tag1', 'Test of tag1'), 'create tag command');

        //
        try {
            $this->assertEquals('', $tagCommand->pushTag('origin', 'tag1'), 'Push tag1 to origin');
        } catch (RunGitCommandException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertContains("fatal: 'origin' does not appear to be a git repository", trim($errorMessage),
            'push tag command error');

    }

    /**
     * Test branch names.
     */
    public function testValidateTagName()
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
}
