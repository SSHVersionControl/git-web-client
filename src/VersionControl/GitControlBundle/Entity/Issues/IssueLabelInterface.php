<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Entity\Issues;

/**
 * IssueLabel.
 */
interface IssueLabelInterface
{
    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get hexColor.
     *
     * @return string
     */
    public function getHexColor();

    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Get issue.
     *
     * @return array
     */
    public function getIssues();
}
