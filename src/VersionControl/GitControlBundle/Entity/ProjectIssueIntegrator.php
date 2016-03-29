<?php

namespace VersionControl\GitControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Project Issue Integrator Entity
 * 
 *
 * @ORM\Table(name="project_issue_integrator", indexes={@ORM\Index(name="fk_project_issue_integrator_project1", columns={"project_id"})})
 * @ORM\Entity(repositoryClass="VersionControl\GitControlBundle\Repository\ProjectIssueIntegratorRepository")
 * @ORM\HasLifecycleCallbacks
 * 
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\DiscriminatorMap({
 *   "Github" = "VersionControl\GithubIssueBundle\Entity\ProjectIssueIntegratorGithub",
 *   "Gitlab" = "VersionControl\GitlabIssueBundle\Entity\ProjectIssueIntegratorGitlab"
 * })
 */
abstract class ProjectIssueIntegrator {
    
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
     * @ORM\Column(name="repo_type", type="string", length=80, nullable=true)
     */
    private $repoType;
    
    /**
     * @var \VersionControl\GitControlBundle\Entity\Project
     *
     * @ORM\ManyToOne(targetEntity="VersionControl\GitControlBundle\Entity\Project")
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
     * Type of repo eg github,gitlab,gitbucket(JIRA)
     * @return string
     */
    public function getRepoType() {
        return $this->repoType;
    }

    /**
     * Project
     * @return \VersionControl\GitControlBundle\Entity\Project
     */
    public function getProject() {
        return $this->project;
    }


    public function setRepoType($repoType) {
        $this->repoType = $repoType;
        return $this;
    }

    public function setProject(\VersionControl\GitControlBundle\Entity\Project $project) {
        $this->project = $project;
        return $this;
    }
    



    

}
