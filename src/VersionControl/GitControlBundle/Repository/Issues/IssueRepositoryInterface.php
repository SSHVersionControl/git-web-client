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

interface IssueRepositoryInterface
{
    /**
     * Finds issues for a state.
     *
     * @param string $keyword
     *
     * @return array of issues
     */
    public function findIssues($keyword, $state = 'open');

    /**
     * Count number of issues by $state and $keyword.
     *
     * @param string $keyword
     * @param string $state
     */
    public function countFindIssues($keyword, $state = 'open');

    /**
     * @param int $id
     */
    public function findIssueById($id);

    /**
     * Gets a new Issue entity.
     *
     * @param type $issue
     *
     * @return VersionControl\GitControlBundle\Entity\Issues\Issue
     */
    public function newIssue();

    /**
     * @param type $issue
     */
    public function createIssue($issue);

    /**
     * @param int $id
     */
    public function reOpenIssue($id);

    /**
     * @param int $id
     */
    public function closeIssue($id);

    /**
     * @param int $issue
     */
    public function updateIssue($issue);

    /**
     * Gets the number of Issues for a milestone by state.
     *
     * @param int    $issueMilestoneId
     * @param string $state            open|closed|blank
     */
    public function countIssuesInMilestones($issueMilestoneId, $state);

    /**
     * Find issues in milestone.
     *
     * @param int    $issueMilestoneId
     * @param string $state            open|closed
     * @param string $keyword          Search string
     */
    public function findIssuesInMilestones($issueMilestoneId, $state, $keyword = false);
}
