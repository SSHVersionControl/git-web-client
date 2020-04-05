<?php
/*
 * This file is part of the GitlabIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitlabIssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use VersionControl\GitControlBundle\Entity\ProjectIssueIntegrator;

/**
 * Project Issue Integrator Entity.
 *
 *
 * @ORM\Table(name="project_issue_integrator_gitlab")
 * @ORM\Entity
 */
class ProjectIssueIntegratorGitlab extends ProjectIssueIntegrator
{
    /**
     * @var string
     *
     * @ORM\Column(name="project_name", type="string", length=255, nullable=true)
     */
    private $projectName;

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
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * API Token. Used to authenticate.
     *
     * @return type
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * Url of server with issues. eg https://www.github.com/.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getProjectName()
    {
        return $this->projectName;
    }

    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;

        return $this;
    }
}
