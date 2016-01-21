<?php

namespace VersionContol\GitControlBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use VersionContol\GitControlBundle\Event\GitAlterFilesEvent;
use VersionContol\GitControlBundle\Utility\GitCommands\GitFilesCommand;


class GitAlterFilesEventListener
{
    /**
     *
     * @var VersionContol\GitControlBundle\Utility\GitCommands\GitFilesCommand 
     */
    protected $gitFilesCommand;
    
    public function __construct(GitFilesCommand $gitFilesCommand) {
        $this->gitFilesCommand = $gitFilesCommand;
    }
    
    public function changeFilePermissions(GitAlterFilesEvent $event)
    {
       $projectEnviroment = $event->getProjectEnviroment();
       $projectEnvironmentFilePerm = $projectEnviroment->getProjectEnvironmentFilePerm();
       if($projectEnvironmentFilePerm !== null){
           if($projectEnvironmentFilePerm->getEnableFilePermissions()){
                $this->gitFilesCommand->setProject($projectEnviroment->getProject());
       
                $branch = $this->gitFilesCommand->getCurrentBranch();

                $files = array();
                if(count($event->getFilesAltered()) > 0){
                     $files = $event->getFilesAltered();
                }else{
                    $fileInfos = $this->gitFilesCommand->listFiles('',$branch,true);

                    foreach($fileInfos as $fileInfo){
                         $files[] =$fileInfo->getFullPath();
                    }
                }

                $this->gitFilesCommand->setFilesPermissions($files
                        ,$projectEnvironmentFilePerm->getFileMode()
                        );
                $this->gitFilesCommand->setFilesOwnerAndGroup($files,$projectEnvironmentFilePerm->getFileOwner(),$projectEnvironmentFilePerm->getFileGroup());
           }
       }
    }
    
}

