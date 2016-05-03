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
use VersionControl\GitControlBundle\Utility\ProjectEnvironmentStorage;
use VersionControl\GitControlBundle\Controller\ProjectController;
use VersionControl\GitControlBundle\Controller\Base\BaseProjectController;

class ProjectEnviromentBeforeListener
{
     /**
     * @var ProjectEnvironmentStorage
     */
    protected $projectEnvironmentStorage;
    
    /**
     * 
     * @param \VersionControl\GitControlBundle\Controller\ProjectController\ProjectEnvironmentStorage $projectEnvironmentStorage
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
            if ($controllerObject instanceof ProjectController || $controllerObject instanceof BaseProjectController) {
             
                $params = $event->getRequest()->attributes->get('_route_params');
 
                if($event->getRequest()->query->get('projenv') && key_exists('id', $params)){
                  $projectEnvironmentId = intval($event->getRequest()->query->get('projenv'));
                  $projectId = intval($params['id']);
                  
                  $this->projectEnvironmentStorage->setProjectEnvironment($projectId, $projectEnvironmentId);
                }  
            }
     }
}
