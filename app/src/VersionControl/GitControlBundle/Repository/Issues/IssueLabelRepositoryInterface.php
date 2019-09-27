<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Repository\Issues;

interface IssueLabelRepositoryInterface
{
    /**
     * Finds issues for a state.
     *
     * @param string $keyword
     *
     * @return array of issues
     */
    public function listLabels();

    /**
     * @param int $id
     */
    public function findLabelById($id);

    /**
     * Gets a new Label entity.
     *
     * @param type $issue
     *
     * @return VersionControl\GitControlBundle\Entity\Labels\Label
     */
    public function newLabel();

    /**
     * @param type $issue
     */
    public function createLabel($issueLabel);

    /**
     * @param int $issue
     */
    public function updateLabel($issueLabel);

    /**
     * @param int $issueLabelId
     */
    public function deleteLabel($issueLabelId);
}
