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

use SplFileInfo;
use VersionControl\GitCommandBundle\GitCommands\Exception\FileStatusException;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidDirectoryException;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidFilePathException;
use VersionControl\GitCommandBundle\Tests\GitCommandTestCase;
use VersionControl\GitCommandBundle\Entity\GitLog;

/**
 * GitFilesCommandTest.
 *
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
        $this->addFile('test', null, 'Test Of List Files');
        $this->addFolder('MoreFiles');
        $this->addFile('test2', 'MoreFiles', 'Test Of file in MoreFiles folder');

        $this->gitCommands->command('commit')->stageAll();
        $this->gitCommands->command('commit')->commit('first commit', 'Paul Schweppe <paulschweppe@gmail.com>');

        $this->addFile('test3', null, 'This file is not commited');

    }

    /**
     * Test list files.
     */
    public function testListFiles()
    {
        $files = $this->gitCommands->command('files')->listFiles('');

        $this->assertCount(3, $files, 'File list count does not equal expected 3. Actual:' . count($files));

        foreach ($files as $file) {
            $this->assertInstanceOf(SplFileInfo::class, $file);
        }

        $this->assertTrue($files[0]->isDir(), 'First file should be a directory');
        $this->assertTrue($files[1]->isFile(), 'Second file should be a file');

        $this->assertInstanceOf(GitLog::class, $files[1]->getGitLog());
        $this->assertNull($files[2]->getGitLog(), 'Third file should not have a git log');

        $errorMessage = '';
        try {
            $this->gitCommands->command('files')->listFiles('../');
        } catch (InvalidDirectoryException $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertContains('Directory path is not valid', trim($errorMessage), 'Invalid Directory error');
    }

    /**
     * Test Get File
     */
    public function testGetFile()
    {
        $file = $this->gitCommands->command('files')->getFile('MoreFiles/test2');
        $this->assertTrue($file->isFile(), '');
        $this->assertInstanceOf(SplFileInfo::class, $file);
        $this->assertInstanceOf(GitLog::class, $file->getGitLog());

        $errorMessage = '';
        try {
            $fileNotExisting = $this->gitCommands->command('files')->getFile('MoreFiles/testDoesNotExist');
        } catch (InvalidDirectoryException $e) {
            $errorMessage = $e->getMessage();
        }

        $this->assertFalse($fileNotExisting->getRealPath(), '');
        //$this->assertContains("Directory path is not valid", trim($errorMessage), 'Invalid Directory error:'.$errorMessage);
    }

    /**
     * Test Read File
     */
    public function testReadFile()
    {
        $file = $this->gitCommands->command('files')->getFile('MoreFiles/test2');
        $fileContent = $this->gitCommands->command('files')->readFile($file);
        $this->assertContains('Test Of file in MoreFiles folder', trim($fileContent),
            'File content did not match:' . $fileContent);

    }

    /**
     * Test malicious file paths
     */
    public function testValidPathStr()
    {

        $filesCommand = $this->gitCommands->command('files');
        $this->assertTrue($filesCommand->ValidPathStr('MoreFiles/test2'), 'Path "MoreFiles/test2" should be valid');
        $this->assertFalse($filesCommand->ValidPathStr('/MoreFiles/test2'), 'Path "/MoreFiles/test2" should be valid');
        $this->assertFalse($filesCommand->ValidPathStr('.git/config'), 'Path ".git/config" should be valid');
        $this->assertFalse($filesCommand->ValidPathStr('/'), 'Path "/" should be valid');
        $this->assertFalse($filesCommand->ValidPathStr('/../'), 'Path "/../" should be valid');
        $this->assertFalse($filesCommand->ValidPathStr('//test'), 'Path "//test" should be valid');
        $this->assertFalse($filesCommand->ValidPathStr('\test'), 'Path "\test" should be valid');

        $this->assertTrue(
            $filesCommand->ValidPathStr('test/test/Ссылка (fce).xml'),
            'Path "test/test/Ссылка (fce).xml" should be valid'
        );

    }

    public function testIsFileIgnored()
    {
        $filesCommand = $this->gitCommands->command('files');
        $this->addFile('ignoretest', null, 'Git should Ignore');
        $this->addFile('.gitignore', null, 'ignoretest');

        $this->assertTrue($filesCommand->isFileIgnored('ignoretest'), 'File ignoretest should be ignored');

        $this->updateFile('.gitignore', null);
        $this->assertFalse($filesCommand->isFileIgnored('test'),
            'File test is not ignored because it is commited already');

        $this->assertFalse($filesCommand->isFileIgnored('notest'), 'File does not exist');

        $this->assertFalse($filesCommand->isFileIgnored('MoreFiles/test2'), 'File exists and should not be ignored');
    }

    public function testIsFileTracked()
    {
        $filesCommand = $this->gitCommands->command('files');
        $this->addFile('ignoretest', null, 'Git should Ignore');
        $this->addFile('.gitignore', null, 'ignoretest');

        $this->assertFalse($filesCommand->isFileTracked('ignoretest'), 'File ignoretest should be ignored');

        $this->updateFile('.gitignore', null);
        $this->assertTrue($filesCommand->isFileTracked('test'),
            'File test is not ignored because it is commited already');

        $this->assertFalse($filesCommand->isFileTracked('notest'), 'File does not exist');

        $this->assertTrue($filesCommand->isFileTracked('MoreFiles/test2'), 'File exists and should not be ignored');
    }

    public function testIgnoreFile()
    {
        $filesCommand = $this->gitCommands->command('files');
        $this->addFile('ignoretest', null, 'Git should Ignore');

        $response = $filesCommand->ignoreFile('ignoretest');

        $this->assertEquals('File added to .gitignore', $response);

        $message = '';
        try {
            $filesCommand->ignoreFile('test');
        } catch (FileStatusException $e) {
            $message = $e->getMessage();
        }

        $this->assertEquals('File path is been tracked. Please un-track file first', $message);

        try {
            $filesCommand->ignoreFile('testFileDoesNotExist');
        } catch (InvalidFilePathException $e) {
            $message = $e->getMessage();
        }

        $this->assertEquals('File path was not valid. Please check that the file exists.', $message);
    }

    public function testUnTrackFileExceptionFromRepoNotCommitted(): void
    {
        $this->expectException(FileStatusException::class);
        $filesCommand = $this->gitCommands->command('files');
        $filesCommand->unTrackFile('test');
    }

    public function testUnTrackFile()
    {
        $filesCommand = $this->gitCommands->command('files');

        //Commit all files
        $this->gitCommands->command('commit')->stageAll();
        $this->gitCommands->command('commit')->commit('second commit', 'Paul Schweppe <paulschweppe@gmail.com>');

        //Untrack file should now work
        $response = $filesCommand->unTrackFile('test');
        $this->assertContains('Please commit to complete the removal', $response);
    }

    public function testFileNolongerTrackAfterUntrackFile()
    {
        $filesCommand = $this->gitCommands->command('files');

        //Commit all files
        $this->gitCommands->command('commit')->stageAll();
        $this->gitCommands->command('commit')->commit('second commit', 'Paul Schweppe <paulschweppe@gmail.com>');

        //Untrack file should now work
        $filesCommand->unTrackFile('test');

        //Add test to .gitignore. Must happen before commit
        $this->updateFile('.gitignore', 'test');

        //Commit change to untracked file
        $this->gitCommands->command('commit')->stageAll();
        $this->gitCommands->command('commit')->commit(
            'Remove file from git index',
            'Paul Schweppe <paulschweppe@gmail.com>'
        );

        //Test that file is no longer tracked
        $this->assertFalse($filesCommand->isFileTracked('test'), 'Test that test file is no longer tracked');
    }
}
