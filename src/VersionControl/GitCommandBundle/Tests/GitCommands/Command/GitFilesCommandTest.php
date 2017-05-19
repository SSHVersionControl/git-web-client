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
 * GitFilesCommandTest.
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitFilesCommandTest extends GitCommandTestCase
{
    /**
     * setUp, called on every method.
     */
    public function setUp()
    {
        $this->initGitCommandsLocal();
        $this->gitCommands->command('init')->initRepository();
        $this->addFile('test',null,'Test Of List Files');
        $this->addFolder('MoreFiles');
        $this->addFile('test2','MoreFiles','Test Of file in MoreFiles folder');
        
        $this->gitCommands->command('commit')->stageAll();
        $this->gitCommands->command('commit')->commit('first commit', 'Paul Schweppe <paulschweppe@gmail.com>');
        
        $this->addFile('test3',null,'This file is not commited');
        
       
    }

    /**
     * Test list files.
     */
    public function testListFiles()
    {
        $files = $this->gitCommands->command('files')->listFiles('');
        
        $this->assertCount(3, $files,'File list count does not equal expected 3. Actual:'.count($files));
        
        foreach($files as $file){
            $this->assertInstanceOf("\SplFileInfo", $file);
        }
        
        $this->assertTrue($files[0]->isDir(), 'First file should be a directory');
        $this->assertTrue($files[1]->isFile(), 'Second file should be a file');
        
        $this->assertInstanceOf("\VersionControl\GitCommandBundle\Entity\GitLog", $files[1]->getGitLog());
        $this->assertNull($files[2]->getGitLog(), 'Third file should not have a git log');
        
        $errorMessage = '';
        try{
            $this->gitCommands->command('files')->listFiles('../');
        }catch(\VersionControl\GitCommandBundle\GitCommands\Exception\InvalidDirectoryException $e){
            $errorMessage = $e->getMessage();
        }
        $this->assertContains("Directory path is not valid", trim($errorMessage), 'Invalid Directory error');
    }
    
    /**
     * Test Get File
     */
    public function testGetFile()
    {
        $file = $this->gitCommands->command('files')->getFile('MoreFiles/test2');
        $this->assertTrue($file->isFile(), '');
        $this->assertInstanceOf("\SplFileInfo", $file);
        $this->assertInstanceOf("\VersionControl\GitCommandBundle\Entity\GitLog", $file->getGitLog());
        
        
        $errorMessage = '';
        try{
            $fileNotExisting = $this->gitCommands->command('files')->getFile('MoreFiles/testDoesNotExist');
        }catch(\VersionControl\GitCommandBundle\GitCommands\Exception\InvalidDirectoryException $e){
            $errorMessage = $e->getMessage();
        }
        
        $this->assertFalse($fileNotExisting->getRealPath(), '');
        //$this->assertContains("Directory path is not valid", trim($errorMessage), 'Invalid Directory error:'.$errorMessage);
    }
    
    


}
