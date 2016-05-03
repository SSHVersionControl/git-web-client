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

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use VersionControl\GitCommandBundle\Event\GitAlterFilesEvent;
use VersionControl\GitCommandBundle\GitCommands\GitCommand;


class GitAlterFilesEventListener
{
    /**
     *
     * @var VersionControl\GitCommandBundle\GitCommands\GitCommands\Command\GitFilesCommand 
     */
    protected $gitCommand;
    
    
    public function __construct(GitCommand $gitCommand) {
        $this->gitCommand = $gitCommand;
    }
    
    public function changeFilePermissions(GitAlterFilesEvent $event)
    {
       $projectEnvironment = $event->getGitEnvironment();
       if(!$projectEnvironment instanceof \VersionControl\GitControlBundle\Entity\ProjectEnvironment){
           throw new \Exception('Git Environment must be a entity of project Environment');
       }
       $projectEnvironmentFilePerm = $projectEnvironment->getProjectEnvironmentFilePerm();
       if($projectEnvironmentFilePerm !== null){
           if($projectEnvironmentFilePerm->getEnableFilePermissions()){
               
                $gitFilesCommand = $this->gitCommand->command('files');
                $gitFilesCommand->overRideGitEnvironment($projectEnvironment);
       
                $branch = $this->gitCommand->command('branch')->getCurrentBranch();

                $files = array();
                if(count($event->getFilesAltered()) > 0){
                     $files = $event->getFilesAltered();
                }else{
                    $fileInfos = $gitFilesCommand->listFiles('',$branch,true);

                    foreach($fileInfos as $fileInfo){
                         $files[] =$fileInfo->getFullPath();
                    }
                }

                $gitFilesCommand->setFilesPermissions($files
                        ,$projectEnvironmentFilePerm->getFileMode()
                        );
                $gitFilesCommand->setFilesOwnerAndGroup($files,$projectEnvironmentFilePerm->getFileOwner(),$projectEnvironmentFilePerm->getFileGroup());
           }
       }
    }
    
}

