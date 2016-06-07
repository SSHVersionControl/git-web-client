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

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use VersionControl\GitControlBundle\Controller\Base\BaseProjectController;
use VersionControl\GitControlBundle\Annotation\ProjectAccess;

use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Project controller.
 *
 * @Route("/project/{id}")
 * 
 */
class ProjectIndexController extends BaseProjectController
{

    /**
     * Allow access by ajax only request
     * @var boolean 
     */
    protected $ajaxOnly = false;
    
    /**
     * Get stats as json object.
     *
     * @Route("/status", name="project_status_ajax")
     * @Method("GET")
     * @ProjectAccess(grantType="VIEW")
     */
    public function statusAction(Request $request)
    {
        //Get latest from updates from remot branches
        $this->gitCommands->command('branch')->fetchAll();
        
        $pushPullCommitCount = $this->gitCommands->command('sync')->commitCountWithRemote($this->branchName);
        
        $statusCount = $this->gitCommands->command('commit')->countStatus();
        
        $response = array(
            'success' => true,
            'pushCount' => $pushPullCommitCount['pushCount'],
            'pullCount' => $pushPullCommitCount['pullCount'],
            'statusCount' => $statusCount
        );
        
        return new JsonResponse($response);
        
    }
    
    /**
     * Lists all Project entities.
     *
     * @Route("/{section}", defaults={"$section" = ""}, name="project")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function indexAction(Request $request,$section= "")
    {
        if($section){
            $section = urldecode($section);
        }
        
        //Get latest from updates from remot branches
        $response = $this->gitCommands->command('branch')->fetchAll();
        
        $pushPullCommitCount = $this->gitCommands->command('sync')->commitCountWithRemote($this->branchName);
        
        $statusCount = $this->gitCommands->command('commit')->countStatus();

        //$this->get('session')->getFlashBag()->add('notice', $response);
        
        return array_merge($this->viewVariables, array(
            'pushPullCommitCount' => $pushPullCommitCount,
            'statusCount' => $statusCount,
            'section' => $section,
            ));
    }
    
   
}
