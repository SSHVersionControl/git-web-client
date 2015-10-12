<?php

namespace VersionContol\GitControlBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use VersionContol\GitControlBundle\Utility\ProjectEnvironmentStorage;
use VersionContol\GitControlBundle\Controller\ProjectController;

class ProjectEnviromentBeforeListener
{
     /**
     * @var ProjectEnvironmentStorage
     */
    protected $projectEnvironmentStorage;
    
    /**
     * 
     * @param \VersionContol\GitControlBundle\Controller\ProjectController\ProjectEnvironmentStorage $projectEnvironmentStorage
     */
    public function __construct(ProjectEnvironmentStorage $projectEnvironmentStorage)
    {
        $this->projectEnvironmentStorage = $projectEnvironmentStorage;     
    }
    
     public function onKernelController(FilterControllerEvent $event)
     {
           $controller = $event->getController();
           if (!is_array($controller)) {
                // not a object but a different kind of callable. Do nothing
                return;
            }

            $controllerObject = $controller[0];
            if ($controllerObject instanceof ProjectController) {
             
                $params = $event->getRequest()->attributes->get('_route_params');
 
                if($event->getRequest()->query->get('projenv') && key_exists('id', $params)){
                  $projectEnvironmentId = intval($event->getRequest()->query->get('projenv'));
                  $projectId = intval($params['id']);
                  
                  $this->projectEnvironmentStorage->setProjectEnvironment($projectId, $projectEnvironmentId);
                }  
            }
     }
}
