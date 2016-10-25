<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Twig\Extension;

use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Utility\ProjectEnvironmentStorage;

/**
 * Twig extension providing filter to get current project environment for project.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class ProjectEnvironmnentStorageExtension extends \Twig_Extension
{
    /**
     * @var ProjectEnvironmentStorage
     */
    protected $projectEnvironmentStorage;

    public function __construct(ProjectEnvironmentStorage $projectEnvironmentStorage)
    {
        $this->projectEnvironmentStorage = $projectEnvironmentStorage;
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'versioncontrol_projectenvironmnentstorageextension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('currentprojectenvironmnent', array($this, 'currentProjectEnvironmnent')),
        );
    }

    /**
     * @param string $colorHex
     *
     * @return string
     */
    public function currentProjectEnvironmnent(Project $project)
    {
        return $this->projectEnvironmentStorage->getProjectEnviromment($project);
    }
}
