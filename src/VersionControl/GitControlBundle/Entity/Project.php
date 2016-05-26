<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitControlBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use VersionControl\DoctrineEncryptBundle\Configuration\Encrypted;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="VersionControl\GitControlBundle\Repository\ProjectRepository")
 * @ORM\Table(name="project")
 */
class Project
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
     * @ORM\Column(name="title", type="string", length=80, nullable=true)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=225, nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(name="path", type="string", length=225, nullable=true)
     */
    private $path;

    /**
     * @var string
     * @ORM\Column(name="ssh", type="boolean",nullable=true)
     */
    private $ssh;
    
    /**
     * @var string
     * @ORM\Column(name="host", type="string", length=225, nullable=true)
     */
    private $host;
    
    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=225, nullable=true)
     */
    private $username;
    
    /**
     * @var string
     * @ORM\Column(name="password", type="string", nullable=true)
     * @Encrypted
     */
    private $password;
    
    /**
     * @var \VersionControl\GitControlBundle\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="VersionControl\GitControlBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     * })
     */
    private $creator;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="VersionControl\GitControlBundle\Entity\UserProjects", mappedBy="project", cascade={"persist"}, orphanRemoval=true )
     * 
     */
    private $userProjects;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="VersionControl\GitControlBundle\Entity\ProjectEnvironment", mappedBy="project", cascade={"persist"}, orphanRemoval=true )
     * @Assert\Valid 
     * @Assert\Count( 
     *   min = "1", 
     *   minMessage = "validate.resourceCurriculum.min",
     *   groups={"publish"})
     */
    private $projectEnvironment;
    



     public function __construct()
    {

        $this->userProjects = new \Doctrine\Common\Collections\ArrayCollection();
        $this->projectEnvironment = new \Doctrine\Common\Collections\ArrayCollection();
    }
    /**
     * Set title
     *
     * @param string $title
     *
     * @return Project
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return Project
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get SSH value
     * @return boolean
     */
    public function getSsh() {
        return $this->ssh;
    }
    
    /**
     * Set to use SSH
     * @param boolean $ssh
     */
    public function setSsh($ssh) {
        $this->ssh = $ssh;
    }

    /**
     * Get SSH host
     * @return string
     */
    public function getHost() {
        return $this->host;
    }
    
    /**
     * Set SSH host
     * @param string $host
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * Get SSH username
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }
    
    /**
     * set SSH username
     * @param type $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * Get SSH password
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set SSH password
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }
    
    /**
     * Add user access to project
     *
     * @param \VersionControl\GitControlBundle\Entity\UserProjects $userProject
     * @return Resource
     */
    public function addUserProjects(\VersionControl\GitControlBundle\Entity\UserProjects $userProject)
    {
        $userProject->setProject($this);
        $this->userProjects[] = $userProject;
    
        return $this;
    }

    /**
     * Remove user access to project
     *
     * @param \VersionControl\GitControlBundle\Entity\UserProjects $userProject
     */
    public function removeUserProjects(\VersionControl\GitControlBundle\Entity\UserProjects $userProject)
    {
        $this->userProjects->removeElement($userProject);
    }
    
    /**
     * The creator of the project
     * @return \VersionControl\GitControlBundle\Entity\User\User 
     */
    public function getCreator() {
        return $this->creator;
    }

    /**
     * Sets the creator of the project
     * @param \VersionControl\GitControlBundle\Entity\User\User $creator
     * @return \VersionControl\GitControlBundle\Entity\Project
     */
    public function setCreator(\VersionControl\GitControlBundle\Entity\User\User $creator) {
        $this->creator = $creator;
        return $this;
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
    
    /**
     * Get array of project enviroments
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjectEnvironment() {
        return $this->projectEnvironment;
    }

    /**
     * Set array of project enviroments
     * @param \Doctrine\Common\Collections\Collection $projectEnvironment
     * @return \VersionControl\GitControlBundle\Entity\Project
     */
    public function setProjectEnvironment(\Doctrine\Common\Collections\Collection $projectEnvironment) {
        $this->projectEnvironment = $projectEnvironment;
        return $this;
    }
    
    /**
     * Add project environment
     *
     * @param \VersionControl\GitControlBundle\Entity\ProjectEnvironment $projectEnvironment
     * @return Resource
     */
    public function addProjectEnvironment(\VersionControl\GitControlBundle\Entity\ProjectEnvironment $projectEnvironment)
    {
        $projectEnvironment->setProject($this);
        $this->projectEnvironment[] = $projectEnvironment;
    
        return $this;
    }

    /**
     * Remove project environment
     *
     * @param \VersionControl\GitControlBundle\Entity\ProjectEnvironment $projectEnvironment
     */
    public function removeProjectEnvironment(\VersionControl\GitControlBundle\Entity\ProjectEnvironment $projectEnvironment)
    {
        $this->projectEnvironment->removeElement($projectEnvironment);
    }




}

