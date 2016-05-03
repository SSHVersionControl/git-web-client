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

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use VersionControl\GitControlBundle\Entity\UserProjects;
use VersionControl\GitControlBundle\Entity\Project;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProjectAccessControlList implements EventSubscriber{
    
    /**
     *
     * @var Symfony\Component\Security\Acl\Model\AclProviderInterface 
     */
    private $container;
    
    public function __construct(ContainerInterface  $container) {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
            'preRemove'
        );
    }
    
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        //$entityManager = $args->getEntityManager();

        // If entity is userproject then update ACL based on roles
        if ($entity instanceof UserProjects) {

            $this->createACLSettings($entity);
        }
    }
    
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        //$entityManager = $args->getEntityManager();

        // If entity is userproject then update ACL based on roles
        if ($entity instanceof UserProjects) {

            $this->createACLSettings($entity);
        }
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // Deletes Acl settings for Project if it is deleted
        if ($entity instanceof Project) {
            $this->deleteACLSettingsForProject($entity);
        }
    }
    
    /**
     * Sets the Access Control Level for the user for this project
     * 
     * @param UserProjects $userProject
     * @throws AccessDeniedException
     */
    protected function createACLSettings(UserProjects $userProject){
        // creating the ACL
        $user = $userProject->getUser();
        $project = $userProject->getProject();
        
        $aclProvider = $this->container->get('security.acl.provider');
        $objectIdentity = ObjectIdentity::fromDomainObject($project);
        // retrieving the security identity of the currently logged-in user
        $securityIdentity = UserSecurityIdentity::fromAccount($user);
        
        try {
            $acl = $aclProvider->findAcl($objectIdentity);
            
            //Delete any Exisitng acls for this users. Only the Username seems to work
            $aces = $acl->getObjectAces();
            foreach($aces as $i => $ace) {
                if ($ace->getSecurityIdentity()->equals($securityIdentity)) {
                //if($ace->getSecurityIdentity()->getUsername() == $user->getUsername()){
                    // Got it! Let's remove it!
                    $acl->deleteObjectAce($i);

                }
            }
        } catch (\Symfony\Component\Security\Acl\Exception\AclNotFoundException $e) {
            $acl = $aclProvider->createAcl($objectIdentity);
        }

        

        
 
        // grant owner access
        if($userProject->getRoles() == 'Reporter'){
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_VIEW);
        }else if($userProject->getRoles() == 'Developer'){
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OPERATOR);
        }else if($userProject->getRoles() == 'Master'){
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_MASTER);
        }else if($userProject->getRoles() == 'Owner'){
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        }else{
            throw new AccessDeniedException("User Role is not valid");
        }
        $aclProvider->updateAcl($acl);
    }
    
    /**
     * Deletes all ACL settings for a project entity.
     * @param Project $project
     */
    protected function deleteACLSettingsForProject(Project $project){
        
        $aclProvider = $this->container->get('security.acl.provider');
        $objectIdentity = ObjectIdentity::fromDomainObject($project);
        $aclProvider->deleteAcl($objectIdentity);

    }
    
    /**
     * Deletes all ACL settings for a user.
     * @param type $user
     * @todo: Figure out how to do this
     */
    protected function deleteACLSettingsForUser( $user){
        
        $aclProvider = $this->container->get('security.acl.provider');
        $securityIdentity = UserSecurityIdentity::fromAccount($user);
        
        //Get all projects
        $userProjects = $user->getUserProjects();
        foreach($userProjects as $userProject){
            $objectIdentity = ObjectIdentity::fromDomainObject($userProject->getProject());
             try {
                $acl = $aclProvider->findAcl($objectIdentity);

                //Delete any Exisitng acls for this users. Only the Username seems to work
                $aces = $acl->getObjectAces();
                foreach($aces as $i => $ace) {
                    if ($ace->getSecurityIdentity()->equals($securityIdentity)) {
                        $acl->deleteObjectAce($i);
                    }
                }
            } catch (\Symfony\Component\Security\Acl\Exception\AclNotFoundException $e) {
            
            }
        }
    }
    
}

