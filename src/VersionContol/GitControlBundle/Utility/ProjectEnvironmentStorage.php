<?php
// src/Acme/UserBundle/Entity/User.php
namespace VersionContol\GitControlBundle\Utility;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Entity\ProjectEnvironment;

/**
 * Description of ProjectEnvironmentSelection
 *
 * @author paul
 */
class ProjectEnvironmentStorage {
   
    /**
     * Session Objects
     * @var Symfony\Component\HttpFoundation\Session\Session 
     */
    protected $session;
    
    /**
     * 
     * @var Doctrine\ORM\EntityManager 
     */
    protected $em;
    
    /**
     * 
     * @param \VersionContol\GitControlBundle\Utility\Symfony\Component\HttpFoundation\Session\Session $session
     */
    public function __construct(Session $session, EntityManager $em) {
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * Need to create a service for these functions
     * @param  integer $project
     * @param integer $projectEnvironmentId
     */
    public function setProjectEnvironment($projectId,$projectEnvironmentId){
        $this->session->set('projectEnvironment'.$projectId,$projectEnvironmentId);
    }
    
    /**
     * 
     * @param \VersionContol\GitControlBundle\Entity\Project $project
     * @return \VersionContol\GitControlBundle\Entity\ProjectEnvironment
     * @throws \Exception
     */
    public function getProjectEnviromment(Project $project){ 
        
        if($this->session->has('projectEnvironment'.$project->getId())){
            $projectEnvironmentId =  $this->session->get('projectEnvironment'.$project->getId());
            
            $currentProjectEnvironment = $this->em->getRepository('VersionContolGitControlBundle:ProjectEnvironment')->find($projectEnvironmentId);
            if($currentProjectEnvironment->getProject()->getId() === $project->getId()){
                
                return $currentProjectEnvironment;
            }else{
                throw new \Exception("Project Id does not match current project");
            }
            
        }else{
            $currentProjectEnvironment = $project->getProjectEnvironment()->first();
        }
        return $currentProjectEnvironment;
    }
    
}


