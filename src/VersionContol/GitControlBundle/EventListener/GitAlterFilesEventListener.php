<?php

namespace VersionContol\GitControlBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use VersionContol\GitControlBundle\Event\GitAlterFilesEvent;
use VersionContol\GitControlBundle\Utility\GitCommands\GitCommand;


class GitAlterFilesEventListener
{
    /**
     *
     * @var VersionContol\GitControlBundle\Utility\GitCommands\Command\GitFilesCommand 
     */
    protected $gitCommand;
    
    
    public function __construct(GitCommand $gitCommand) {
        $this->gitCommand = $gitCommand;
    }
    
    public function changeFilePermissions(GitAlterFilesEvent $event)
    {
       $projectEnviroment = $event->getProjectEnviroment();
       $projectEnvironmentFilePerm = $projectEnviroment->getProjectEnvironmentFilePerm();
       if($projectEnvironmentFilePerm !== null){
           if($projectEnvironmentFilePerm->getEnableFilePermissions()){
               
                $gitFilesCommand = $this->gitCommand->command('files');
                $gitFilesCommand->setProject($projectEnviroment->getProject());
       
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

