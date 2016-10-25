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

/**
 * Twig extension to get the parent directory in the File listing.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class ParentDirectoryExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'versioncontrol_parentdirectory';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('parentDir', array($this, 'parentDirectory')),
        );
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    public function parentDirectory($dir)
    {
        $parentDir = dirname($dir);
        if ($parentDir !== '.') {
            return dirname($dir);
        }

        return '';
    }
}
