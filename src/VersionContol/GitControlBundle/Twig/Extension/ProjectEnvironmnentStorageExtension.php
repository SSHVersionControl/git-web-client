<?php

namespace VersionContol\GitControlBundle\Twig\Extension;

use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Utility\ProjectEnvironmentStorage;

/**
 * Twig extension providing filters for locale-aware formatting of numbers and currencies.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2013 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ProjectEnvironmnentStorageExtension extends \Twig_Extension {
    
     /**
     * @var ProjectEnvironmentStorage
     */
    protected $projectEnvironmentStorage;
    
    public function __construct(ProjectEnvironmentStorage $projectEnvironmentStorage)
    {
        $this->projectEnvironmentStorage = $projectEnvironmentStorage;     
    }
    /**
     * {@inheritDoc}
     */
    public function getName() {
            return 'versioncontrol_projectenvironmnentstorageextension';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('currentprojectenvironmnent', array($this, 'currentProjectEnvironmnent')),
        );
    }

    /**
     * 
     * @param string $colorHex
     * @return string
     */
    public function currentProjectEnvironmnent(Project $project) {

        return $this->projectEnvironmentStorage->getProjectEnviromment($project);
    }
     
}
