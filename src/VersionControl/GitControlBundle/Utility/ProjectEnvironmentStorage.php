<?php
// src/VersionControl/GitControlBundle/Utility/ProjectEnvironmentStorage.php

/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Utility;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Entity\ProjectEnvironment;

/**
 * Description of ProjectEnvironmentSelection.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class ProjectEnvironmentStorage
{
    /**
     * Session Objects.
     *
     * @var Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param \VersionControl\GitControlBundle\Utility\Symfony\Component\HttpFoundation\Session\Session $session
     */
    public function __construct(Session $session, EntityManager $em)
    {
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * Need to create a service for these functions.
     *
     * @param int $project
     * @param int $projectEnvironmentId
     */
    public function setProjectEnvironment($projectId, $projectEnvironmentId)
    {
        $this->session->set('projectEnvironment'.$projectId, $projectEnvironmentId);
    }

    /**
     * @param \VersionControl\GitControlBundle\Entity\Project $project
     *
     * @return \VersionControl\GitControlBundle\Entity\ProjectEnvironment
     *
     * @throws \Exception
     */
    public function getProjectEnviromment(Project $project)
    {
        if ($this->session->has('projectEnvironment'.$project->getId())) {
            $projectEnvironmentId = $this->session->get('projectEnvironment'.$project->getId());

            $currentProjectEnvironment = $this->em->getRepository('VersionControlGitControlBundle:ProjectEnvironment')->find($projectEnvironmentId);
            if ($currentProjectEnvironment->getProject()->getId() === $project->getId()) {
                return $currentProjectEnvironment;
            } else {
                throw new \Exception('Project Id does not match current project');
            }
        } else {
            $currentProjectEnvironment = $project->getProjectEnvironment()->first();
        }

        return $currentProjectEnvironment;
    }
}
