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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\Request;
use VersionControl\GitControlBundle\Annotation\ProjectAccess;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Project tag controller.
 *
 * @Route("/project/{id}/tag")
 */
class ProjectTagController extends BaseProjectController
{
    /**
     * List Tags.
     *
     * @Route("s/", name="project_tags")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function tagsAction()
    {
        $tags = $this->gitCommands->command('tag')->getTags();

        $defaultData =array();
        $form = $this->createNewTagForm($this->project, $defaultData);

        return array_merge($this->viewVariables, array(
            'form' => $form->createView(),
            'tags' => $tags,
        ));
    }

    /**
     * Creates a new tag
     *
     * @Route("/create/", name="project_tag")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:ProjectTag:tags.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function createTagAction(Request $request, $id)
    {
        $form = $this->createNewTagForm($this->project);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $tagVersion = $data['name'];
            $tagMessage = $data['message'];
            $tagCommit = $data['commit'];
            try {
                $response = $this->gitCommands->command('tag')->createAnnotatedTag($tagVersion, $tagMessage, $tagCommit);
                $this->get('session')->getFlashBag()->add('notice', $response);

                return $this->redirect($this->generateUrl('project_tags', array('id' => $id)));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            }
        }

        $tags = $this->gitCommands->command('tag')->getTags();
        
        return array_merge($this->viewVariables, array(
            'form' => $form->createView(),
            'tags' => $tags,
        ));
    }


    /**
     * Creates a form to edit a Project entity.
     *
     * @param Project $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createNewTagForm($project, $defaultData = array(), $formAction = 'project_tag')
    {

        //$defaultData = array();
        $form = $this->createFormBuilder($defaultData, array(
                    'action' => $this->generateUrl($formAction, array('id' => $project->getId())),
                    'method' => 'POST',
                ))
                ->add('name', TextType::class, array(
                    'label' => 'Tag Name', 'required' => true, 'constraints' => array(
                        new NotBlank(),
                    ), )
                )
                ->add('message', TextType::class, array(
                    'label' => 'Tag Message', 'required' => true, 'constraints' => array(
                        new NotBlank(),
                    ), )
                )
                ->add('commit', HiddenType::class)
                ->getForm();

        $form->add('submit', SubmitType::class, array('label' => 'Add Tag'));

        return $form;
    }
    
    
    /**
     * Form to handle pushing tags to remote
     *
     * @Route("/push/{tag}/{responseType}", name="project_push_tag", defaults={"responseType" = null})
     * @Method({"GET", "POST"})
     * @Template()
     * @ProjectAccess(grantType="MASTER")
     */
    public function pushTagAction(Request $request, $tag, $responseType)
    {
        $success = true;
        $form = $this->createPushTagForm($this->project,$tag);
        $form->add('push', SubmitType::class, array('label' => 'Push'));
        
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $formData = $form->getData();
                $remote = $formData['remote'];
                $tag = $formData['tag'];
                
                try {
                    $response = $this->gitCommands->command('tag')->pushTag($remote, $tag);

                    $this->get('session')->getFlashBag()->add('notice', $response);
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add('error', $e->getMessage());
                }

               //return $this->redirect($this->generateUrl('project_push', array('id' => $id)));
                $data = ['redirect' => $this->generateUrl('project_tags', array('id' => $this->project->getId()))];

                return $this->viewHandler($data, 'json', true);
               
            } else {
                $success = false;
            }
        }

        //Remote Server choice
        $gitRemoteVersions = $this->gitCommands->command('sync')->getRemoteVersions();
        
        $content = $this->render('VersionControlGitControlBundle:ProjectTag:pushTag.html.twig', 
             array_merge($this->viewVariables, array(
                'remoteVersions' => $gitRemoteVersions,
                'push_form' => $form->createView()
            )
        ));

        return $this->viewHandler($content, $responseType, $success);
    }
    
    /**
     * Creates a form to edit a Project entity.
     *
     * @param Project $project The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createPushTagForm($project, $tag)
    {
        //Remote Server choice
        $gitRemoteVersions = $this->gitCommands->command('sync')->getRemoteVersions();
        $remoteChoices = array();
        foreach ($gitRemoteVersions as $remoteVersion) {
            $remoteChoices[$remoteVersion[0].'('.$remoteVersion[1].')'] = $remoteVersion[0];
        }

        $firstOrigin = reset($remoteChoices);

        $defaultData = array('tag' => $tag);
        
        $form = $this->createFormBuilder($defaultData, array(
                'action' => $this->generateUrl('project_push_tag', array('id' => $project->getId(), 'tag'=> $tag, 'responseType' => 'json')),
                'method' => 'POST',
            ))
            ->add('remote', ChoiceType::class, array(
                'label' => 'Remote Server', 'choices' => $remoteChoices, 'data' => $firstOrigin, 'required' => false, 'choices_as_values' => true, 'constraints' => array(
                    new NotBlank(),
                ), )
            )
            ->add('tag', HiddenType::class)
            ->getForm();

        return $form;
    }

}
