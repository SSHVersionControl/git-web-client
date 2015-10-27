<?php

namespace VersionContol\GitControlBundle\Twig\Extension;

/**
 * Twig extension providing filters for locale-aware formatting of numbers and currencies.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2013 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ParentDirectoryExtension extends \Twig_Extension {
    
    /**
     * {@inheritDoc}
     */
    public function getName() {
            return 'versioncontrol_parentdirectory';
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('parentDir', array($this, 'parentDirectory')),
        );
    }

    /**
     * 
     * @param string $dir
     * @return string
     */
    public function parentDirectory($dir) {

        $parentDir = dirname($dir);
        if($parentDir !== '.'){
            return dirname($dir);
        }
        return '';

    }
     
}
