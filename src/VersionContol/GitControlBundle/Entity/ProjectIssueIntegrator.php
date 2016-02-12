<?php

namespace VersionContol\GitControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Project Issue Integrator Entity
 * 
 *
 * @ORM\Table(name="project_issue_integrator", indexes={@ORM\Index(name="fk_project_issue_integrator_project1", columns={"project_id"})})
 * @ORM\Entity(repositoryClass="VersionContol\GitControlBundle\Repository\ProjectIssueIntegratorRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProjectIssueIntegrator {
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="repo_name", type="string", length=255, nullable=true)
     */
    private $repoName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="owner_name", type="string", length=255, nullable=true)
     */
    private $ownerName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="api_token", type="string", length=255, nullable=true)
     */
    private $apiToken;
    
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;
    
    /**
     * @var string
     *
     * @ORM\Column(name="repo_type", type="string", length=80, nullable=true)
     */
    private $repoType;
    
    /**
     * @var \VersionContol\GitControlBundle\Entity\Project
     *
     * @ORM\ManyToOne(targetEntity="VersionContol\GitControlBundle\Entity\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private $project;
    
    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Git Respository Name
     * @return string
     */
    public function getRepoName() {
        return $this->repoName;
    }

    /**
     * Git Repository Owner Name
     * @return type
     */
    public function getOwnerName() {
        return $this->ownerName;
    }

    /**
     * API Token. Used to authenticate
     * @return type
     */
    public function getApiToken() {
        return $this->apiToken;
    }

    /**
     * Url of server with issues. eg https://www.github.com/
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Type of repo eg github,gitlab,gitbucket(JIRA)
     * @return string
     */
    public function getRepoType() {
        return $this->repoType;
    }

    /**
     * Project
     * @return \VersionContol\GitControlBundle\Entity\Project
     */
    public function getProject() {
        return $this->project;
    }

    public function setRepoName($repoName) {
        $this->repoName = $repoName;
        return $this;
    }

    public function setOwnerName($ownerName) {
        $this->ownerName = $ownerName;
        return $this;
    }

    public function setApiToken($apiToken) {
        $this->apiToken = $apiToken;
        return $this;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    public function setRepoType($repoType) {
        $this->repoType = $repoType;
        return $this;
    }

    public function setProject(\VersionContol\GitControlBundle\Entity\Project $project) {
        $this->project = $project;
        return $this;
    }
    



    

}
