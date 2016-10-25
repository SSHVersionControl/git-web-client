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

use VersionControl\GitCommandBundle\Entity\FileInfo;
use VersionControl\GitCommandBundle\Entity\RemoteFileInfo;
use VersionControl\GitCommandBundle\Entity\GitLog;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;

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
     * @return array remoteFileInfo/fileInfo
     */
    public function listFiles($dir, $branch = 'master', $gitFilesOnly = false)
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

    public function getFile($path, $branch = 'master')
    {
        $fileInfo = $this->getFileInfo($path);
        $fileLastLog = $this->getLog(1, $branch, $fileInfo->getGitPath());
        if (count($fileLastLog) > 0) {
            $fileInfo->setGitLog($fileLastLog[0]);
        }

        return $fileInfo;
    }

    protected function getFileInfo($path)
    {
        $fileInfo = null;

        if ($this->validPathStr($path) === false) {
            throw new \Exception('Directory path is not valid. Possible security issue.');
        }

        $basePath = $this->addEndingSlash($this->command->getGitEnvironment()->getPath());

        if ($this->command->getGitEnvironment()->getSsh() === true) {
            //Remote Directory Listing

            $fileData = $this->command->getSftpProcess()->getFileStats($basePath.$path);

            $fileData['filename'] = basename($path);
            $fileData['fullPath'] = $basePath.$path;
            $fileData['gitPath'] = $path;
            $fileInfo = new RemoteFileInfo($fileData);
        } else {
            $splFileInfo = new \SPLFileInfo($basePath.$path);
            $splFileInfo->setInfoClass('\VersionControl\GitCommandBundle\Entity\FileInfo');

            $newFileInfo = $splFileInfo->getFileInfo();

            $newFileInfo->setGitPath($basePath.$path);

            $fileInfo = $newFileInfo;
        }

        return $fileInfo;
    }

    /**
     * Adds Ending slash where needed for unix and windows paths.
     *
     * @param string $path
     *
     * @return string
     */
    protected function addEndingSlash($path)
    {
        $slash_type = (strpos($path, '\\') === 0) ? 'win' : 'unix';
        $last_char = substr($path, strlen($path) - 1, 1);
        if ($last_char != '/' and $last_char != '\\') {
            // no slash:
            $path .= ($slash_type == 'win') ? '\\' : '/';
        }

        return $path;
    }

    /**
     * Sort files by directory then name.
     *
     * @param array $fileArray
     */
    protected function sortFilesByDirectoryThenName(array &$fileArray)
    {
        usort($fileArray, function ($a, $b) {
            if ($a->isDir()) {
                if ($b->isDir()) {
                    return strnatcasecmp($a->getFilename(), $b->getFilename());
                } else {
                    return -1;
                }
            } else {
                if ($b->isDir()) {
                    return 1;
                } else {
                    return strnatcasecmp($a->getFilename(), $b->getFilename());
                }
            }
        });
    }

    /**
     * Get files in directory locally and remotely.
     *
     * @param string $dir full path to directory
     *
     * @return array of files
     */
    public function getFilesInDirectory($dir)
    {
        if ($this->validPathStr($dir) === false) {
            throw new \Exception('Directory path is not valid. Possible security issue.');
        }

        $files = array();
        $basePath = $this->addEndingSlash($this->command->getGitEnvironment()->getPath());
        $relativePath = $dir;
        if ($relativePath) {
            $relativePath = $this->addEndingSlash($relativePath);
        }

        if ($this->command->getGitEnvironment()->getSsh() === true) {
            //Remote Directory Listing
            $directoryList = $this->command->getSftpProcess()->getDirectoryList($basePath.$relativePath);

            foreach ($directoryList as $filename => $fileData) {
                if ($filename !== '.' && $filename !== '..' && $filename !== '.git') {
                    $fileData['fullPath'] = $basePath.rtrim($relativePath, '/').'/'.$filename;
                    $fileData['gitPath'] = $relativePath.$filename;

                    $remoteFileInfo = new RemoteFileInfo($fileData);
                    if ($remoteFileInfo->isFile()) {
                    }
                    $files[] = $remoteFileInfo;
                }
            }
        } else {
            //Local Directory Listing
            $directoryIterator = new \DirectoryIterator($basePath.$dir);
            $directoryIterator->setInfoClass('\VersionControl\GitCommandBundle\Entity\FileInfo');

            foreach ($directoryIterator as $fileInfo) {
                if (!$fileInfo->isDot() && $fileInfo->getFilename() !== '.git') {
                    $newFileInfo = $fileInfo->getFileInfo();
                    $newFileInfo->setGitPath($relativePath.$fileInfo->getFilename());

                    $files[] = $newFileInfo;
                }
            }
        }

        $this->sortFilesByDirectoryThenName($files);

        return $files;
    }

    public function readFile($file)
    {
        //$basePath = $this->addEndingSlash($this->command->getGitEnvironment()->getPath());
        $fileContents = '';

        if ($this->command->getGitEnvironment()->getSsh() === true) {
            $fileContents = $this->command->getSftpProcess()->getFileContents($file->getFullPath());
        } else {
            $fileContents = file_get_contents($file->getFullPath());
        }

        return $fileContents;
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
     * @todo Possible improvement: Should it rawurldecode the string first to check if any of these characters is encoded?
     */
    public function validPathStr($theFile)
    {
        if (strpos($theFile, '//') === false && strpos($theFile, '\\') === false && !preg_match('#(?:^\\.\\.|/\\.\\./|[[:cntrl:]])#u', $theFile)) {
            return true;
        }

        return false;
    }

    public function setFilesPermissions($filePaths, $mode = '0775')
    {
        $basePath = trim($this->addEndingSlash($this->command->getGitEnvironment()->getPath()));
        if ($this->command->getGitEnvironment()->getSsh() === true) {
            //Remote Directory Listing
            $permissions = octdec($mode);
            foreach ($filePaths as $filepath) {
                $this->command->runCommand(sprintf('chmod -R %s %s', $mode, escapeshellarg($filepath)));
            }
        } else {
            //Run local chmod
        }
    }

    public function setFilesOwnerAndGroup($filePaths, $user = 'www-data', $group = 'fr_user')
    {
        $basePath = trim($this->addEndingSlash($this->command->getGitEnvironment()->getPath()));
        if ($this->command->getGitEnvironment()->getSsh() === true) {
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
        } else {
            //Run local chmod
        }
    }

    /**
     * Gets the git log (history) of commits
     * Currenly limits to the last 20 commits.
     *
     * @return GitLog|array
     */
    public function getLog($count = 20, $branch = 'master', $fileName = false)
    {
        $logs = array();
        $logData = '';
        try {
            //$logData = $this->command->runCommand('git --no-pager log --pretty=format:"%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s" -'.intval($count).' '.$branch);
            $command = 'git --no-pager log -m "--pretty=format:\'%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s\'" -'.intval($count).' ';
            if ($branch && $branch != '(No Branch)') {
                $command .= escapeshellarg(trim($branch)).' ';
            } else {
                $command .= '-- ';
            }
            if ($fileName !== false) {
                $command .= ' -- '.escapeshellarg($fileName);
            } else {
                $command .= ' --';
            }
            $logData = $this->command->runCommand($command);
        } catch (RunGitCommandException $e) {
            if ($this->getObjectCount() == 0) {
                return $logs;
            } else {
                throw new RunGitCommandException('Error in get log Command:'.$e->getMessage());
                //Throw exception
            }
        }

        $lines = $this->splitOnNewLine($logData);

        if (is_array($lines) && count($lines) > 0) {
            foreach ($lines as $line) {
                if (trim($line)) {
                    $logs[] = new GitLog($line);
                }
            }
        }

        return $logs;
    }

    /**
     * Check if a file is ignored
     * EXIT STATUS.
     0
     One or more of the provided paths is ignored.

     1
     None of the provided paths are ignored.

     128
     A fatal error was encountered.
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function isFileIgnored($filePath)
    {
        try {
            $response = $this->command->runCommand(sprintf('git check-ignore %s', escapeshellarg($filePath)));
        } catch (RunGitCommandException $e) {
            if ($this->command->getLastExitStatus() == 128) {
                throw $e;
            } elseif ($this->command->getLastExitStatus() == 1) {
                $response = false;
            } else {
                $response = true;
            }
        }

        return $response ? true : false;
    }

    /**
     * Check if a file is been tracked by git.
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function isFileTracked($filePath)
    {
        $response = $this->command->runCommand(sprintf('git ls-files %s', escapeshellarg($filePath)));

        return $response ? true : false;
    }

    public function ignoreFile($filePath)
    {
        $response = '';
        if ($this->fileExists($filePath)) {
            if ($this->filePathIsIgnored($filePath) === false) {
                $response = $this->addToGitIgnore($filePath);
            } else {
                $response = "File in .gitignore already.\n";
            }

            $response .= $this->command->runCommand(sprintf('git rm --cached %s', escapeshellarg($filePath)));

            $response .= "\n Please commit to complete the removal of this file from git index";
        } else {
            throw new \Exception('File path was not valid. Please check that the file exists.');
        }

        return $response;
    }

    /**
     * Checks if a file exists.
     *
     * @param type $filePath FilePath excluding base path
     *
     * @return type
     */
    public function fileExists($filePath)
    {
        $fileExists = false;
        $basePath = trim($this->addEndingSlash($this->command->getGitEnvironment()->getPath()));
        if ($this->command->getGitEnvironment()->getSsh() === true) {
            $fileExists = $this->command->getSftpProcess()->fileExists($basePath.$filePath);
        } else {
            $fileExists = file_exists($basePath.$filePath);
        }

        return $fileExists;
    }

    public function filePathIsIgnored($filePath)
    {
        $fileIgnored = false;
        $ignoreFiles = $this->getGitIgnoreFile();

        foreach ($ignoreFiles as $ignoreFilePath) {
            if ($ignoreFilePath === '/'.$filePath) {
                $fileIgnored = true;
            }
        }

        return $fileIgnored;
    }

    /**
     * Gets the contents of gitignore file and
     * splits on newline and returns it as an array.
     *
     * @return array
     */
    public function getGitIgnoreFile()
    {
        $ignoreFiles = array();

        $basePath = trim($this->addEndingSlash($this->command->getGitEnvironment()->getPath()));
        $fileData['fullPath'] = '.gitignore';
        $fileData['gitPath'] = '.gitignore';

        $remoteFileInfo = new RemoteFileInfo($fileData);

        $contents = $this->readFile($remoteFileInfo);
        $ignoreFiles = $this->splitOnNewLine($contents, true);

        return $ignoreFiles;
    }

    public function addToGitIgnore($filePath)
    {
        if ($this->fileExists('.gitignore')) {
            //Update git ignore file
            if ($this->command->getGitEnvironment()->getSsh() === true) {
                //$fileContents = $this->command->getSftpProcess()->appendToFile('.gitignore',PHP_EOL.$filePath.PHP_EOL);
                $response = $this->command->runCommand(sprintf('echo %s >> .gitignore', escapeshellarg(PHP_EOL.$filePath)));
            } else {
                $fileContents = file_put_contents('.gitignore', PHP_EOL.$filePath, FILE_APPEND);
            }
        } else {
            //create file
        }

        return "File added to .gitignore\n";
    }
}
