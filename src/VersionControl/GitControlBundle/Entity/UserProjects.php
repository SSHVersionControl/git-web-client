<?php

namespace VersionControl\GitControlBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="VersionControl\GitControlBundle\Repository\UserProjectsRepository")
 * @ORM\Table(name="user_projects")
 */
class UserProjects
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    private $id;
    
    /**
     * @var string
     * @ORM\Column(name="roles", type="string", length=225, nullable=true)
     */
    private $roles;
    
    /**
     * @var \VersionControl\GitControlBundle\Entity\Project
     *
     * @ORM\ManyToOne(targetEntity="VersionControl\GitControlBundle\Entity\Project", inversedBy="userProjects")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private $project;
    
    /**
     * @var \VersionControl\GitControlBundle\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="VersionControl\GitControlBundle\Entity\User\User", inversedBy="userProjects")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ver_user_id", referencedColumnName="id")
     * })
     */
    private $user;
    
    /**
     * Entity Id
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function setRoles($roles) {
        $this->roles = $roles;
        return $this;
    }
    
    public function getProject() {
        return $this->project;
    }

    public function setProject(\VersionControl\GitControlBundle\Entity\Project $project) {
        $this->project = $project;
        return $this;
    }
    
     public function getUser() {
        return $this->user;
    }

    public function setUser(\VersionControl\GitControlBundle\Entity\User\User $user) {
        $this->user = $user;
        return $this;
    }


    
    
}

