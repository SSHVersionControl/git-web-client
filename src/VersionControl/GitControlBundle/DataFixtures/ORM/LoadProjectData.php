<?php


namespace VersionControl\GitControlBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Entity\UserProjects;
use VersionControl\GitControlBundle\Entity\ProjectEnvironment;


class LoadProjectData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     *
     * @var type 
     */
    private $rootDir;
    
    /**
     * FOS user manager
     * @var FOS\UserBundle\Doctrine\UserManager 
     */
    private $userManager;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $em)
    {
        $kernel = $this->container->get('kernel');
        $this->rootDir = dirname($kernel->getRootDir());
        //$this->userManager = $this->container->get('fos_user.user_manager');
        
        if($kernel->getEnvironment() == 'test'){
            //Create Test User
            
            $this->loadTestData($em);
        }
    }
    
    protected function loadTestData(ObjectManager $em){
                
        $user = $this->loadUserData($em,'test','test','info@test.com','Test Test');

        //Set Creator
        $project = new Project();
        $project->setTitle('Test Project');
        $project->setDescription('Project used for testing only');
        $project->setCreator($user);

        //Set Access and Roles
        $userProjectAccess = new UserProjects();
        $userProjectAccess->setUser($user);
        $userProjectAccess->setRoles('Owner');
        $project->addUserProjects($userProjectAccess);

        $em->persist($project);
        $em->flush();
        
        $projectEnvironment = new ProjectEnvironment();
        $projectEnvironment->setProject($project);
        $projectEnvironment->setTitle('Current Project');
        $projectEnvironment->setDescription('Test project environment showing this systems git repo');
        $projectEnvironment->setPath($this->rootDir);
        
        $em->persist($projectEnvironment);
        $em->flush();
        
    }
    
    protected function loadUserData(ObjectManager $em, $username,$password,$email,$name){
        $userManager = $this->container->get('fos_user.user_manager');
        
        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setName($name);
        
        $user->setEnabled((Boolean) true);
        $user->addRole('ROLE_ADMIN');
        //$user->setSuperAdmin((Boolean) true);
        
        $userManager->updateUser($user, true);
        return $user;
        
    }
    
    
}

