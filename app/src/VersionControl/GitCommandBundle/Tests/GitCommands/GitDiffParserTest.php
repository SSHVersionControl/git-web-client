<?php
/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Tests\GitCommands\Command;

use VersionControl\GitCommandBundle\Tests\GitCommandTestCase;
use VersionControl\GitCommandBundle\GitCommands\GitDiffParser;
use VersionControl\GitCommandBundle\Entity\GitDiff;

/**
 * Description of GitBranchCommandTest.
 *
 * @author fr_user
 */
class GitDiffParserTest extends GitCommandTestCase
{
    /**
     * setUp, called on every method.
     */
    public function setUp()
    {
        
    }

    /**
     * Test Getting commit files.
     */
    public function testGetCommitDiff()
    {
        $diffString = 
                'diff --git a/src/VersionControl/GitControlBundle/Controller/ProjectHistoryController.php b/src/VersionControl/GitControlBundle/Controller/ProjectHistoryController.php
index b9b5a2a..53b3b2d 100644
--- a/src/VersionControl/GitControlBundle/Controller/ProjectHistoryController.php
+++ b/src/VersionControl/GitControlBundle/Controller/ProjectHistoryController.php
@@ -50,6 +50,7 @@ class ProjectHistoryController extends BaseProjectController

         $currentPage = $request->query->get(\'page\', 1);
         $filter = false;
+        $keyword = \'\';
         //Search
         /*$keyword = $request->query->get(\'keyword\', false);
         $filter= $request->query->get(\'filter\', false);
@@ -103,7 +104,7 @@ class ProjectHistoryController extends BaseProjectController
             \'totalCount\' => $this->gitLogCommand->getTotalCount(),
             \'limit\' => $this->gitLogCommand->getLimit(),
             \'currentPage\' => $this->gitLogCommand->getPage()+1,
-            //\'keyword\' => $keyword,
+            \'keyword\' => $keyword,
             \'filter\' => $filter,
             \'searchForm\' => $searchForm->createView()
         ));';
        
        $diffParser = new GitDiffParser($diffString);
        $diffs = $diffParser->parse();
        
        $this->assertCount(1, $diffs,'Diff count does not equal expected 17. Actual:'.count($diffs).print_r($diffs,true));
        foreach ($diffs as $diff) {
            $this->assertInstanceOf(GitDiff::class, $diff);
        }
        
    }
}
