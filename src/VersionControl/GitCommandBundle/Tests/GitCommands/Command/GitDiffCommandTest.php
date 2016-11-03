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
class GitDiffCommandTest extends GitCommandTestCase
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
        $this->updateFile('test', 'Test content some more data');
        $this->gitCommands->command('commit')->stageAll();
        $this->gitCommands->command('commit')->commit('Second commit', 'Paul Schweppe <paulschweppe@gmail.com>');
        $this->updateFile('test', 'Test Data has changed');
    }
    
    public function testGetPreviousCommitHash(){
        
        $previousCommitHash = $this->gitCommands->command('diff')->getPreviousCommitHash();
        $this->assertTrue((strlen($previousCommitHash) === 7),'Previous Commit hash does not equal 7 characters: '.$previousCommitHash);
        
    }

    /**
     * Test Getting commit files.
     */
    public function testGetDiffFile()
    {

        $diffs = $this->gitCommands->command('diff')->getDiffFile('test');
        
        $this->assertCount(1, $diffs,'Diff count does not equal expected 17. Actual:'.count($diffs).print_r($diffs,true));
        foreach ($diffs as $diff) {
            $this->assertInstanceOf("\VersionControl\GitCommandBundle\Entity\GitDiff", $diff);
        }
    }
    
    public function testGetDiffFileBetweenCommits(){
        
        $previousCommitHash = $this->gitCommands->command('diff')->getPreviousCommitHash();
       
        $diffs = $this->gitCommands->command('diff')->getDiffFileBetweenCommits('test',$previousCommitHash,'HEAD');
        foreach ($diffs as $diff) {
            $this->assertInstanceOf("\VersionControl\GitCommandBundle\Entity\GitDiff", $diff);
        }       
    }
    
    public function testGetFilesInCommit(){
        $filesCommitted = $this->gitCommands->command('diff')->getFilesInCommit('HEAD');
        
        $this->assertCount(1, $filesCommitted,'Only one file should have been commited. Actual:'.count($filesCommitted));

        foreach ($filesCommitted as $commitFile) {
            $this->assertInstanceOf("VersionControl\GitCommandBundle\Entity\GitCommitFile", $commitFile);
        }       
    }
    
    
    
}
