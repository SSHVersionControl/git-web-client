<?php

namespace VersionControl\GitCommandBundle\Tests;

use Gitlab\Model\Commit;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use VersionControl\GitCommandBundle\GitCommands\GitCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\Common\Cache\ArrayCache;
use VersionControl\GitCommandBundle\Logger\GitCommandLogger;
use VersionControl\GitCommandBundle\Tests\GitCommands\GitUserFixture;

/**
 * Description of GitCommandTestCase.
 *
 * @author fr_user
 */
class GitCommandTestCase extends TestCase
{
    /**
     * @var GitCommand
     */
    protected $gitCommands;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var SimpleGitEnvironment
     */
    protected $gitEnvironment;

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @return GitCommand|GitCommands
     */
    protected function getGitCommands()
    {
        if ($this->gitCommands == null) {
            $this->gitCommands = new GitCommand();

            $this->assertInstanceOf(GitCommand::class, $this->gitCommands);

            $logger = $this->getMockBuilder(GitCommandLogger::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->gitCommands->setLogger($logger);

            $arrayCache = $this->getMockBuilder(ArrayCache::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->gitCommands->setCache($arrayCache);

            $dispatch = $this->getMockBuilder(EventDispatcher::class)
                ->disableOriginalConstructor()
                ->getMock();

            $this->gitCommands->setDispatcher($dispatch);

            $this->gitCommands->setGitUser(new GitUserFixture());
        }

        return $this->gitCommands;
    }

    /**
     */
    protected function initGitCommandsLocal()
    {
        $tempDir = realpath(sys_get_temp_dir());
        $tempFullPathName = tempnam($tempDir, 'versioncontrol');
        $this->path = $tempFullPathName;
        @unlink($this->path);
        $fs = new Filesystem();
        $fs->mkdir($this->path);

        $this->gitEnvironment = new SimpleGitEnvironment();
        $this->gitEnvironment->setId(1);
        $this->gitEnvironment->setPath($this->path);
        $this->gitEnvironment->setSsh(false);

        $this->getGitCommands();
        $this->gitCommands->setGitEnvironment($this->gitEnvironment);
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove($this->path);

    }

    /**
     * @param string $name file name
     * @param string|null $folder folder name
     * @param null $content content
     */
    protected function addFile($name, $folder = null, $content = null)
    {
        $path = $this->path;

        $filename = $folder == null ?
            $path . DIRECTORY_SEPARATOR . $name :
            $path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $name;
        $handle = fopen($filename, 'wb');
        $fileContent = $content == null ? 'test content' : $content;
        $this->assertNotSame(false, fwrite($handle, $fileContent), sprintf('unable to write the file %s', $name));
        fclose($handle);
    }

    /**
     * remove file from repo.
     *
     * @param string $name
     */
    protected function removeFile($name)
    {
        $filename = $this->path . DIRECTORY_SEPARATOR . $name;
        $this->assertTrue(unlink($filename));
    }

    /**
     * update a file in the repository.
     *
     * @param string $name file name
     * @param string $content content
     */
    protected function updateFile($name, $content)
    {
        $filename = $this->path . DIRECTORY_SEPARATOR . $name;
        $this->assertNotSame(false, file_put_contents($filename, $content));
    }

    /**
     * rename a file in the repository.
     *
     * @param string $originName file name
     * @param string $targetName new file name
     */
    protected function renameFile($originName, $targetName)
    {
        $origin = $this->path . DIRECTORY_SEPARATOR . $originName;
        $target = $this->path . DIRECTORY_SEPARATOR . $targetName;
        $fs = new Filesystem();
        $fs->rename($origin, $target);
    }

    /**
     * @param string $name name
     */
    protected function addFolder($name)
    {
        $fs = new Filesystem();
        $fs->mkdir($this->path . DIRECTORY_SEPARATOR . $name);
    }

    /**
     * mock the caller.
     *
     * @param string $command command
     * @param string $output output
     *
     * @return MockObject
     */
    protected function getMockCaller($command, $output)
    {
        $mock = $this->getMock('GitElephant\Command\Caller\CallerInterface');
        $mock
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mock));
        $mock
            ->expects($this->any())
            ->method('getOutputLines')
            ->willReturn($output);

        return $mock;
    }

    protected function getMockContainer()
    {
        return $this->getMock('GitElephant\Command\CommandContainer');
    }

    protected function addCommandToMockContainer(MockObject $container, $commandName)
    {
        $container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo($commandName))
            ->willReturn($this->getMockCommand());
    }

    protected function addOutputToMockRepo(MockObject $repo, $output)
    {
        $repo
            ->expects($this->any())
            ->method('getCaller')
            ->willReturn($this->getMockCaller('', $output));
    }

    protected function getMockCommand()
    {
        $command = $this->getMock('Command', array('showCommit'));
        $command
            ->expects($this->any())
            ->method('showCommit')
            ->willReturn('');

        return $command;
    }

    protected function doCommitTest(
        Commit $commit,
        $sha,
        $tree,
        $author,
        $committer,
        $emailAuthor,
        $emailCommitter,
        $datetimeAuthor,
        $datetimeCommitter,
        $message
    ) {
        $this->assertInstanceOf('GitElephant\Objects\Commit', $commit);
        $this->assertEquals($sha, $commit->getSha());
        $this->assertEquals($tree, $commit->getTree());
        $this->assertInstanceOf('GitElephant\Objects\Author', $commit->getAuthor());
        $this->assertEquals($author, $commit->getAuthor()->getName());
        $this->assertEquals($emailAuthor, $commit->getAuthor()->getEmail());
        $this->assertInstanceOf('GitElephant\Objects\Author', $commit->getCommitter());
        $this->assertEquals($committer, $commit->getCommitter()->getName());
        $this->assertEquals($emailCommitter, $commit->getCommitter()->getEmail());
        $this->assertInstanceOf('\Datetime', $commit->getDatetimeAuthor());
        $this->assertEquals($datetimeAuthor, $commit->getDatetimeAuthor()->format('U'));
        $this->assertInstanceOf('\Datetime', $commit->getDatetimeCommitter());
        $this->assertEquals($datetimeCommitter, $commit->getDatetimeCommitter()->format('U'));
        $this->assertEquals($message, $commit->getMessage()->getShortMessage());
    }
}
