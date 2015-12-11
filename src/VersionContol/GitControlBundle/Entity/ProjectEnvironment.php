<?php

namespace VersionContol\GitControlBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use VMelnik\DoctrineEncryptBundle\Configuration\Encrypted;
use Symfony\Component\Validator\Constraints as Assert;
use VersionContol\GitControlBundle\Validator\Constraints as VersionContolAssert;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="VersionContol\GitControlBundle\Repository\ProjectEnvironmentRepository")
 * @ORM\Table(name="project_environment")
 * @VersionContolAssert\SshDetails
 */
class ProjectEnvironment
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
     * @var \VersionContol\GitControlBundle\Entity\Project
     *
     * @ORM\ManyToOne(targetEntity="VersionContol\GitControlBundle\Entity\Project", inversedBy="projectEnvironment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private $project;
    
    /**
     * @var \VersionContol\GitControlBundle\Entity\ProjectEnvironmentFilePerm
     *
     * @ORM\OneToOne(targetEntity="VersionContol\GitControlBundle\Entity\ProjectEnvironmentFilePerm", inversedBy="projectEnvironment", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_environment_file_perm_id", referencedColumnName="id")
     * })
     */
    private $projectEnvironmentFilePerm;

    public function __construct()
    {

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
        if (!is_null($password)) {
            $this->password = $password;
        }
    }
    
    /**
     * Set project
     *
     * @param \VersionContol\GitControlBundle\Entity\Project $project
     *
     * @return Issue
     */
    public function setProject(\VersionContol\GitControlBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \VersionContol\GitControlBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
    
    public function getProjectEnvironmentFilePerm() {
        return $this->projectEnvironmentFilePerm;
    }

    public function setProjectEnvironmentFilePerm(\VersionContol\GitControlBundle\Entity\ProjectEnvironmentFilePerm $projectEnvironmentFilePerm) {
        $this->projectEnvironmentFilePerm = $projectEnvironmentFilePerm;
        return $this;
    }


    

}

