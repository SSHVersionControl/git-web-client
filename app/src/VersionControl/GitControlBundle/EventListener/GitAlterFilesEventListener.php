<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\EventListener;

use Exception;
use VersionControl\GitCommandBundle\Event\GitAlterFilesEvent;
use VersionControl\GitCommandBundle\GitCommands\Command\GitFilesCommand;
use VersionControl\GitCommandBundle\GitCommands\GitCommand;
use VersionControl\GitControlBundle\Entity\ProjectEnvironment;

class GitAlterFilesEventListener
{
    /**
     * @var GitFilesCommand
     */
    protected $gitCommand;

    public function __construct(GitCommand $gitCommand)
    {
        $this->gitCommand = $gitCommand;
    }

    public function changeFilePermissions(GitAlterFilesEvent $event): void
    {
        $projectEnvironment = $event->getGitEnvironment();
        if (!$projectEnvironment instanceof ProjectEnvironment) {
            throw new Exception('Git Environment must be a entity of project Environment');
        }
        $projectEnvironmentFilePerm = $projectEnvironment->getProjectEnvironmentFilePerm();
        if ($projectEnvironmentFilePerm !== null) {
            if ($projectEnvironmentFilePerm->getEnableFilePermissions()) {
                $gitFilesCommand = $this->gitCommand->command('files');
                $gitFilesCommand->overRideGitEnvironment($projectEnvironment);

                $branch = $this->gitCommand->command('branch')->getCurrentBranch();

                $files = array();
                if (count($event->getFilesAltered()) > 0) {
                    $files = $event->getFilesAltered();
                } else {
                    $fileInfos = $gitFilesCommand->listFiles('', $branch, true);

                    foreach ($fileInfos as $fileInfo) {
                        $files[] = $fileInfo->getFullPath();
                    }
                }

                $gitFilesCommand->setFilesPermissions($files, $projectEnvironmentFilePerm->getFileMode());

                $gitFilesCommand->setFilesOwnerAndGroup(
                    $files,
                    $projectEnvironmentFilePerm->getFileOwner(),
                    $projectEnvironmentFilePerm->getFileGroup()
                );
            }
        }
    }
}
