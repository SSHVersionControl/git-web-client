<?php

namespace VersionControl\GithubIssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use VersionContol\GitControlBundle\Entity\ProjectIssueIntegrator;
/**
 * Project Issue Integrator Entity
 * 
 *
 * @ORM\Table(name="project_issue_integrator_github")
 * @ORM\Entity
 * 
 */
class ProjectIssueIntegratorGithub extends ProjectIssueIntegrator{
    
    
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
     * Constructor
     */
    public function __construct()
    {
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

    

}
