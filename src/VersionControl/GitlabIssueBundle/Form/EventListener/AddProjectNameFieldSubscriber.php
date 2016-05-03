<?php
/*
 * This file is part of the GitlabIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace  VersionControl\GitlabIssueBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use VersionControl\GitlabIssueBundle\DataTransformer\GitlabProjectToEntityTransformer;


class AddProjectNameFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event)
    {
        $projectIssueIntegratorGitlab = $event->getData();
        $form = $event->getForm();

        
        
        if ($projectIssueIntegratorGitlab && $projectIssueIntegratorGitlab instanceof \VersionControl\GitlabIssueBundle\Entity\ProjectIssueIntegratorGitlab) {

           $form->add('projectName','gitlab_project_choice',array(
                    'choices' => $this->getProjectChoices($projectIssueIntegratorGitlab),
                    'multiple' => false,   // Multiple selection allowed
                    'placeholder' => 'Choose a projecton Gitlab',
                    'required' => false,
                    'choices_as_values' => true,
                    'choice_label' => function($gitLabProject) {
                        if($gitLabProject){
                            return $gitLabProject->getName();
                        }
                        return;
                    },
                    'choice_value' => function($gitLabProject) {
                         if($gitLabProject){
                           return $gitLabProject->getId();
                         }return;
                       },
                    
                ));
        }
    }
    
    protected function getProjectChoices(\VersionControl\GitlabIssueBundle\Entity\ProjectIssueIntegratorGitlab $projectIssueIntegrator){
        $client = new \Gitlab\Client(rtrim($projectIssueIntegrator->getUrl(),'/').'/api/v3/'); // change here
        $client->authenticate($projectIssueIntegrator->getApiToken(), \Gitlab\Client::AUTH_URL_TOKEN);
        $choices = array();
        $dataResponse = $client->api('projects')->all(1,200);
        $gitlabProjectToEntityTransformer = new GitlabProjectToEntityTransformer();
        foreach($dataResponse as $gitLabProject){
            $choices[] = $gitlabProjectToEntityTransformer->transform($gitLabProject);
        }
        
        return $choices;    
  
    }
    
}

