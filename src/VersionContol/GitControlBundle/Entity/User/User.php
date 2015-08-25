<?php
// src/Acme/UserBundle/Entity/User.php

namespace VersionContol\GitControlBundle\Entity\User;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="ver_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     *
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Please enter your name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max=255,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $name;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="VersionContol\GitControlBundle\Entity\UserProjects", mappedBy="user", cascade={"persist"} )
     * 
     */
    private $userProjects;

    /**
     * Is admin Base on roles eg ROLE_ADMIN
     * @var boolean 
     */
    private $admin;
    
    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->userProjects = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * Add user access to project
     *
     * @param \VersionContol\GitControlBundle\Entity\UserProjects $userProject
     * @return Resource
     */
    public function addUserProjects(\VersionContol\GitControlBundle\Entity\UserProjects $userProject)
    {
        $userProject->setUser($this);
        $this->userProjects[] = $userProject;
    
        return $this;
    }

    /**
     * Remove user access to project
     *
     * @param \VersionContol\GitControlBundle\Entity\UserProjects $userProject
     */
    public function removeUserProjects(\VersionContol\GitControlBundle\Entity\UserProjects $userProject)
    {
        $this->userProjects->removeElement($userProject);
    }

    
    /**
     * Get all user access to project
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserProjects() {
        return $this->userProjects;
    }

    /**
     * Set all user access to projects
     * @param \Doctrine\Common\Collections\Collection $userProjects
     */
    public function setUserProjects(\Doctrine\Common\Collections\Collection $userProjects) {
        $this->userProjects = $userProjects;
    }
    
    public function getAdmin() {
        
        return $this->hasRole('ROLE_ADMIN');
    }

    public function setAdmin($admin) {
        $this->admin = $admin;
        $this->setRoles(array('ROLE_ADMIN'));
        return $this;
    }




}

