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

use DirectoryIterator;
use Exception;
use RuntimeException;
use SPLFileInfo;
use VersionControl\GitCommandBundle\Entity\FileInfoInterface;
use VersionControl\GitCommandBundle\Entity\RemoteFileInfo;
use VersionControl\GitCommandBundle\Entity\GitLog;
use VersionControl\GitCommandBundle\GitCommands\Exception\FileStatusException;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidArgumentException;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidDirectoryException;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidFilePathException;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;
use VersionControl\GitCommandBundle\Entity\FileInfo;

/**
 * Description of GitFilesCommand.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitFilesCommand extends AbstractGitCommand
{
    /**
     * List files in directory with git log.
     *
     * @param string $dir
     *
     * @param string $branch
     * @param bool $gitFilesOnly
     *
     * @return array remoteFileInfo/fileInfo
     * @throws InvalidDirectoryException
     * @throws Exception
     */
    public function listFiles($dir, $branch = 'master', $gitFilesOnly = false): array
    {
        $files = array();
        $fileList = $this->getFilesInDirectory($dir);

        foreach ($fileList as $fileInfo) {
            $fileLastLog = $this->getLog(1, $branch, $fileInfo->getGitPath());

            if (count($fileLastLog) > 0) {
                $fileInfo->setGitLog($fileLastLog[0]);
                $files[] = $fileInfo;
            } elseif ($gitFilesOnly === false) {
                $files[] = $fileInfo;
            }
        }

        return $files;
    }

    /**
     * @param $path
     * @param string $branch
     *
     * @return SPLFileInfo|RemoteFileInfo
     * @throws InvalidDirectoryException
     * @throws Exception
     */
    public function getFile($path, $branch = 'master')
    {
        $fileInfo = $this->getFileInfo($path);
        $fileLastLog = $this->getLog(1, $branch, $fileInfo->getGitPath());
        if (count($fileLastLog) > 0) {
            $fileInfo->setGitLog($fileLastLog[0]);
        }

        return $fileInfo;
    }

    /**
     * @param $path
     *
     * @return SplFileInfo|RemoteFileInfo
     * @throws InvalidDirectoryException
     */
    protected function getFileInfo($path)
    {

        if ($this->validPathStr($path) === false) {
            throw new InvalidDirectoryException('Directory path is not valid. Possible security issue.');
        }

        $basePath = $this->addEndingSlash($this->command->getGitEnvironment()->getPath());

        if ($this->command->getGitEnvironment()->getSsh() === true) {
            //Remote Directory Listing

            $fileData = $this->command->getSftpProcess()->getFileStats($basePath . $path);

            $fileData['filename'] = basename($path);
            $fileData['fullPath'] = $basePath . $path;
            $fileData['gitPath'] = $path;

            return new RemoteFileInfo($fileData);
        }

        $splFileInfo = new SPLFileInfo($basePath . $path);
        $splFileInfo->setInfoClass(FileInfo::class);

        $newFileInfo = $splFileInfo->getFileInfo();
        if (!$newFileInfo instanceof FileInfo) {
            throw new RuntimeException(sprintf('File info must be a instance of "%s"', FileInfo::class));
        }

        $newFileInfo->setGitPath($basePath . $path);

        return $newFileInfo;
    }

    /**
     * Adds Ending slash where needed for unix and windows paths.
     *
     * @param string $path
     *
     * @return string
     */
    protected function addEndingSlash($path): string
    {
        $slash_type = (strpos($path, '\\') === 0) ? 'win' : 'unix';
        $last_char = $path[strlen($path) - 1];
        if ($last_char !== '/' && $last_char !== '\\') {
            // no slash:
            $path .= ($slash_type === 'win') ? '\\' : '/';
        }

        return $path;
    }

    /**
     * Sort files by directory then name.
     *
     * @param array $fileArray
     */
    protected function sortFilesByDirectoryThenName(array &$fileArray): void
    {
        usort($fileArray, static function (FileInfoInterface $a, FileInfoInterface $b) {
            if ($a->isDir()) {
                if ($b->isDir()) {
                    return strnatcasecmp($a->getFilename(), $b->getFilename());
                }

                return -1;
            }

            if ($b->isDir()) {
                return 1;
            }

            return strnatcasecmp($a->getFilename(), $b->getFilename());
        });
    }

    /**
     * Get files in directory locally and remotely.
     *
     * @param string $dir full path to directory
     *
     * @return array of files
     * @throws InvalidDirectoryException
     */
    public function getFilesInDirectory($dir): array
    {
        if ($this->validPathStr($dir) === false) {
            throw new InvalidDirectoryException('Directory path is not valid. Possible security issue.');
        }

        $files = array();
        $basePath = $this->addEndingSlash($this->command->getGitEnvironment()->getPath());
        $relativePath = $dir;
        if ($relativePath) {
            $relativePath = $this->addEndingSlash($relativePath);
        }

        if ($this->command->getGitEnvironment()->getSsh() === true) {
            //Remote Directory Listing
            $directoryList = $this->command->getSftpProcess()->getDirectoryList($basePath . $relativePath);

            foreach ($directoryList as $filename => $fileData) {
                if ($filename !== '.' && $filename !== '..' && $filename !== '.git') {
                    $fileData['fullPath'] = $basePath . rtrim($relativePath, '/') . '/' . $filename;
                    $fileData['gitPath'] = $relativePath . $filename;

                    $remoteFileInfo = new RemoteFileInfo($fileData);

                    $files[] = $remoteFileInfo;
                }
            }
        } else {
            //Local Directory Listing
            $directoryIterator = new DirectoryIterator($basePath . $dir);
            $directoryIterator->setInfoClass(FileInfo::class);

            foreach ($directoryIterator as $fileInfo) {
                if (!$fileInfo->isDot() && $fileInfo->getFilename() !== '.git') {
                    $newFileInfo = $fileInfo->getFileInfo();
                    if (!$newFileInfo instanceof FileInfo) {
                        throw new RuntimeException(sprintf('File info must be a instance of "%s"', FileInfo::class));
                    }
                    $newFileInfo->setGitPath($relativePath . $fileInfo->getFilename());

                    $files[] = $newFileInfo;
                }
            }
        }

        $this->sortFilesByDirectoryThenName($files);

        return $files;
    }

    /**
     * Read Git File
     *
     * @param FileInfoInterface $file
     *
     * @return string file contents
     */
    public function readFile(FileInfoInterface $file): string
    {
        if ($this->command->getGitEnvironment()->getSsh() === true) {
            return $this->command->getSftpProcess()->getFileContents($file->getFullPath());
        }

        return file_get_contents($file->getFullPath());
    }

    /**
     * Checks for malicious file paths.
     *
     * Returns TRUE if no '//', '..', '\' or control characters are found in the $theFile.
     * This should make sure that the path is not pointing 'backwards' and further doesn't contain double/back slashes.
     * So it's compatible with the UNIX style path strings valid for TYPO3 internally.
     *
     * @param string $theFile File path to evaluate
     *
     * @return bool TRUE, $theFile is allowed path string, FALSE otherwise
     *
     * @see http://php.net/manual/en/security.filesystem.nullbytes.php
     *
     * @todo Possible improvement: Should it rawurldecode the string first to check if any of these characters is
     * encoded?
     */
    public function validPathStr($theFile): bool
    {
        return strpos($theFile, '//') === false
            && strpos($theFile, '\\') === false
            && strpos($theFile, '/') !== 0
            && strpos($theFile, '\\') !== 0
            && preg_match('#(?:^\\.\\.|/\\.\\./|.git|[[:cntrl:]])#u', $theFile) === 0;
    }

    /**
     * @param $filePaths
     * @param string $mode
     *
     * @throws RunGitCommandException
     */
    public function setFilesPermissions($filePaths, $mode = '0775'): void
    {
        if (false === $this->command->getGitEnvironment()->getSsh()) {
            return;
        }
        //Remote Directory Listing
        foreach ($filePaths as $filepath) {
            $this->command->runCommand(sprintf('chmod -R %s %s', $mode, escapeshellarg($filepath)));
        }
    }

    /**
     * @param $filePaths
     * @param string $user
     * @param string $group
     *
     * @throws RunGitCommandException
     */
    public function setFilesOwnerAndGroup($filePaths, $user = 'www-data', $group = 'fr_user'): void
    {
        if (false === $this->command->getGitEnvironment()->getSsh()) {
            return;
        }

        //Remote Directory Listing
        if (trim($user)) {
            foreach ($filePaths as $filepath) {
                $this->command->runCommand(sprintf('chown -R %s %s', $user, escapeshellarg($filepath)));
            }
        }
        if (trim($group)) {
            foreach ($filePaths as $filepath) {
                $this->command->runCommand(sprintf('chgrp -R %s %s', $group, escapeshellarg($filepath)));
            }
        }
    }

    /**
     * Gets the git log (history) of commits
     * Currenly limits to the last 20 commits.
     *
     * @param int $count
     * @param string $branch
     * @param bool $fileName
     *
     * @return GitLog|array
     * @throws Exception
     */
    public function getLog($count = 20, $branch = 'master', $fileName = false)
    {
        $gitLogCommand = $this->command->command('log');

        $gitLogCommand->setLogCount($count);
        $gitLogCommand->setBranch($branch);
        $gitLogCommand->setPath($fileName);

        return $gitLogCommand->execute()->getResults();
    }

    /**
     * Check if a file is ignored
     * EXIT STATUS.
     * 0
     * One or more of the provided paths is ignored.
     *
     * 1
     * None of the provided paths are ignored.
     *
     * 128
     * A fatal error was encountered.
     *
     * @param string $filePath
     *
     * @return bool
     * @throws RuntimeException
     * @throws RunGitCommandException
     */
    public function isFileIgnored($filePath): bool
    {
        try {
            $response = $this->command->runCommand(sprintf('git check-ignore %s', escapeshellarg($filePath)));
        } catch (RunGitCommandException $e) {
            if ($this->command->getLastExitStatus() === 128) {
                throw $e;
            }

            $response = $this->command->getLastExitStatus() !== 1;
        }

        return $response ? true : false;
    }

    /**
     * Check if a file is been tracked by git.
     *
     * @param string $filePath
     *
     * @return bool
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function isFileTracked($filePath): bool
    {
        $response = $this->command->runCommand(sprintf('git ls-files %s', escapeshellarg($filePath)));

        return trim($response) !== '';
    }

    /**
     * Ignore a file by adding to gitignore
     *
     * @param string $filePath
     *
     * @return string
     * @throws RunGitCommandException
     * @throws RuntimeException
     * @throws FileStatusException
     * @throws InvalidFilePathException
     */
    public function ignoreFile($filePath): string
    {
        if (false === $this->fileExists($filePath)) {
            throw new InvalidFilePathException('File path was not valid. Please check that the file exists.');
        }

        if ($this->isFileTracked($filePath)) {
            throw new FileStatusException('File path is been tracked. Please un-track file first');
        }

        if ($this->isFileIgnored($filePath) === false) {
            return $this->addToGitIgnore($filePath);
        }

        throw new FileStatusException('File path is already ignored');
    }

    /**
     * Removes file path from git index
     *
     * Update your .gitignore file – for instance, add a folder you don't want to track to .gitignore .
     * git rm -r --cached . – Remove all tracked files, including wanted and unwanted. Your code will be safe as
     * long as you have saved locally.
     * git add . – All files will be added back in, except those in .gitignore .
     *
     * @param string $filePath
     *
     * @return string
     * @throws RunGitCommandException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws FileStatusException
     * @throws InvalidFilePathException
     */
    public function unTrackFile($filePath): string
    {
        if (false === $this->fileExists($filePath)) {
            throw new InvalidFilePathException('File path was not valid. Please check that the file exists.');
        }

        if (false === $this->isFileTracked($filePath)) {
            throw new FileStatusException('File path is not been tracked');
        }

        $statusCount = $this->command->command('commit')->countStatus();

        if ($statusCount > 0) {
            throw new FileStatusException('Please commit all files first');
        }

        $response = $this->command->runCommand(sprintf('git rm --cached %s', escapeshellarg($filePath)));
        $response .= "\n Please commit to complete the removal of this file from git index";

        return $response;
    }

    /**
     * Checks if a file exists.
     *
     * @param string $filePath FilePath excluding base path
     *
     * @return bool
     */
    public function fileExists($filePath): bool
    {
        $basePath = trim($this->addEndingSlash($this->command->getGitEnvironment()->getPath()));
        if ($this->command->getGitEnvironment()->getSsh() === true) {
            return $this->command->getSftpProcess()->fileExists($basePath . $filePath);
        }

        return file_exists($basePath . $filePath);
    }

    /**
     * @param $filePath
     *
     * @return bool
     */
    public function filePathIsIgnored($filePath): bool
    {
        $ignoreFiles = $this->getGitIgnoreFile();

        foreach ($ignoreFiles as $ignoreFilePath) {
            if ($ignoreFilePath === '/' . $filePath) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the contents of gitignore file and
     * splits on newline and returns it as an array.
     *
     * @return array
     */
    public function getGitIgnoreFile(): array
    {
        $fileData['fullPath'] = '.gitignore';
        $fileData['gitPath'] = '.gitignore';

        $remoteFileInfo = new RemoteFileInfo($fileData);

        $contents = $this->readFile($remoteFileInfo);

        return $this->splitOnNewLine($contents);
    }

    /**
     * @param $filePath
     *
     * @return string
     * @throws RunGitCommandException
     */
    public function addToGitIgnore($filePath): string
    {
        if (false === $this->fileExists('.gitignore')) {
            //create file
        }
        //Update git ignore file
        if ($this->command->getGitEnvironment()->getSsh() === true) {
            $this->command->runCommand(
                sprintf('echo %s >> .gitignore', escapeshellarg(PHP_EOL . $filePath))
            );

            return 'File added to .gitignore';
        }

        file_put_contents('.gitignore', PHP_EOL . $filePath, FILE_APPEND);

        return 'File added to .gitignore';
    }
}
