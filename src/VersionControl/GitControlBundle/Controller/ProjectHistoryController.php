<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Controller;

use VersionControl\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Utility\GitCommands;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\Request;
use VersionControl\GitControlBundle\Annotation\ProjectAccess;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Project controller.
 *
 * @Route("/project/{id}/history")
 */
class ProjectHistoryController extends BaseProjectController
{
    /**
     * @var GitCommand
     */
    protected $gitLogCommand;

    /**
     * Displays the project commit history for the current branch.
     *
     * @Route("/", name="project_log")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function listAction(Request $request, $id)
    {
        $currentPage = $request->query->get('page', 1);
        $filter = false;
        $keyword = '';
        //Search
        /*$keyword = $request->query->get('keyword', false);
        $filter= $request->query->get('filter', false);
        if($keyword !== false && trim($keyword) !== ''){
            if($filter !== false){
                if($filter === 'author'){
                    $this->gitLogCommand->setFilterByAuthor($keyword);
                }elseif($filter === 'content'){
                    $this->gitLogCommand->setFilterByContent($keyword);
                }else{
                    $this->gitLogCommand->setFilterByMessage($keyword);
                }
            }
        }*/

        $searchForm = $this->createSearchForm();
        $searchForm->handleRequest($request);

        if ($searchForm->isValid()) {
            $data = $searchForm->getData();

            $keyword = $data['keyword'];
            $filter = $data['filter'];
            $branch = $data['branch'];

            if ($keyword !== false && trim($keyword) !== '') {
                if ($filter !== false) {
                    if ($filter === 'author') {
                        $this->gitLogCommand->setFilterByAuthor($keyword);
                    } elseif ($filter === 'content') {
                        $this->gitLogCommand->setFilterByContent($keyword);
                    } else {
                        $this->gitLogCommand->setFilterByMessage($keyword);
                    }
                }
            }

            $this->gitLogCommand->setBranch($branch)
                ->setPage(($currentPage - 1));
        } else {
            $this->gitLogCommand->setBranch($this->branchName)
                ->setPage(($currentPage - 1));
        }

        $gitLogs = $this->gitLogCommand->execute()->getResults();

        return array_merge($this->viewVariables, array(

            'gitLogs' => $gitLogs,
            'totalCount' => $this->gitLogCommand->getTotalCount(),
            'limit' => $this->gitLogCommand->getLimit(),
            'currentPage' => $this->gitLogCommand->getPage() + 1,
            'keyword' => $keyword,
            'filter' => $filter,
            'searchForm' => $searchForm->createView(),
        ));
    }

    /**
     * Show Git commit diff.
     *
     * @Route("/commit/{commitHash}", name="project_commitdiff")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function commitHistoryAction($id, $commitHash)
    {
        $gitDiffCommand = $this->gitCommands->command('diff');

        $this->gitLogCommand
                ->setLogCount(1)
                ->setCommitHash($commitHash);

        //$gitLog = $this->gitFilesCommands->getCommitLog($commitHash,$this->branchName);
        $gitLog = $this->gitLogCommand->execute()->getFirstResult();

        //Get git Diff
        $files = $gitDiffCommand->getFilesInCommit($commitHash);

        return array_merge($this->viewVariables, array(
            'log' => $gitLog,
            //'diffs' => $gitDiffs,
            'files' => $files,
            'commitHash' => $commitHash,
        ));
    }

    /**
     * Show Git commit diff.
     *
     * @Route("/commitfile/{commitHash}/{filePath}/{diffCommitHash}", name="project_commitfilediff" , defaults={"diffCommitHash" = 0})
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function fileDiffAction($id, $commitHash, $filePath, $diffCommitHash)
    {
        $gitDiffCommand = $this->gitCommands->command('diff');

        $difffile = urldecode($filePath);

        if ($diffCommitHash) {
            $previousCommitHash = $diffCommitHash;
        } else {
            $previousCommitHash = $gitDiffCommand->getPreviousCommitHash($commitHash, $difffile);
        }
        if (!$previousCommitHash) {
            $previousCommitHash = 'HEAD';
        }

        $gitDiffs = $gitDiffCommand->getDiffFileBetweenCommits($difffile, $previousCommitHash, $commitHash);

        $this->gitLogCommand
                ->setLogCount(60)
                ->setCommitHash($commitHash)
                ->setPath($difffile)
                ->setLimit(60);

        $gitPreviousLogs = $this->gitLogCommand->execute()->getResults();

        //First element is current commit so need to remove first element
        array_shift($gitPreviousLogs);

        return array_merge($this->viewVariables, array(
            'diffs' => $gitDiffs,
            'previousLogs' => $gitPreviousLogs,
            'commitHash' => $commitHash,
            'diffCommitHash' => $previousCommitHash,
            'filePath' => $difffile,
        ));
    }

    /**
     * Show Git commit diff.
     *
     * @Route("/checkout-file/{commitHash}/{filePath}", name="project_checkout_file")
     * @Method("GET")
     * @ProjectAccess(grantType="VIEW")
     */
    public function checkoutFileAction($id, $commitHash, $filePath)
    {
        $gitUndoCommand = $this->gitCommands->command('undo');

        $file = urldecode($filePath);
        try {
            $response = $gitUndoCommand->checkoutFile($file, $commitHash);

            $this->get('session')->getFlashBag()->add('notice', $response);
            $this->get('session')->getFlashBag()->add('warning', 'Make sure to commit the changes.');
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('project_commitdiff', array('id' => $id, 'commitHash' => $commitHash)));
    }

    /**
     * @param int $id Project Id
     */
    public function initAction($id, $grantType = 'VIEW')
    {
        $redirectUrl = parent::initAction($id, $grantType);
        if ($redirectUrl) {
            return $redirectUrl;
        }

        $this->gitLogCommand = $this->gitCommands->command('log');
    }

    /**
     * Creates a form to edit a Project entity.
     *
     * @param Project $project The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm()
    {

        //$remoteChoices = array();
        //foreach($gitRemoteVersions as $remoteVersion){
        //    $remoteChoices[$remoteVersion[0]] = $remoteVersion[0].'('.$remoteVersion[1].')';
        //}

        //Local Branch choice
        $branches = $this->gitCommands->command('branch')->getBranches(true);
        $branchChoices = array();
        foreach ($branches as $branchName) {
            $branchChoices[$branchName] = $branchName;
        }

        //Current branch
        $currentBranch = $this->gitCommands->command('branch')->getCurrentBranch();

        $defaultData = array('branch' => $currentBranch);
        $form = $this->createFormBuilder($defaultData, array(
                'action' => $this->generateUrl('project_log', array('id' => $this->project->getId())),
                'method' => 'GET',
            ))
            ->add('branch', ChoiceType::class, array(
                'label' => 'Branch', 'choices' => $branchChoices, 'preferred_choices' => array($currentBranch), 'data' => trim($currentBranch), 'required' => false, 'choices_as_values' => true, 'constraints' => array(
                    //new NotBlank()
                ), )
            )
            ->add('filter', HiddenType::class)
            ->add('keyword', TextType::class, array('required' => false))
            ->getForm();

        //$form->add('submitMain', 'submit', array('label' => 'Push'));
        return $form;
    }
}
