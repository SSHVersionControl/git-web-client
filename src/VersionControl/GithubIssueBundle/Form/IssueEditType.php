<?php

namespace VersionControl\GithubIssueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IssueEditType extends AbstractType
{
    
    protected $issueManager;
    
    public function __construct($issueManager) {
        $this->issueManager = $issueManager;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('status','choice',array('label' => 'State'
                    ,'choices'  => array('open' => 'Open', 'closed' => 'Close')
                    ,'required' => false
                    ,'empty_value' => 'Please select a State')
                    )
            ->add('issueMilestone','choice',array(
                    'choices' => $this->getIssueMilestoneChoices(),
                    'multiple' => false,   // Multiple selection allowed
                    //'expanded' => true,   // Render as checkboxes
                    'placeholder' => 'Choose a milestone',
                    'required' => false,
                    'choices_as_values' => true,
                    //'property' => 'title', // Assuming that the entity has a "name" property
                    'choice_label' => function($issueMilestone) {
                        if($issueMilestone){
                            return $issueMilestone->getTitle();
                        }return;
                    },
                    'choice_value' => function($issueMilestone) {
                        if($issueMilestone){
                            return $issueMilestone->getId();
                        }return;
                    },
                    //'class' => 'VersionContol\GitControlBundle\Entity\IssueMilestone',
                    /*'query_builder' => function (IssueMilestoneRepository $er) use ($project) {
                        return $er->createQueryBuilder('a')
                            ->where('a.project = :project')
                            ->setParameter('project', $project)
                            ->orderBy('a.id', 'ASC');
                    },*/
                ))
            ->add('issueLabel','choice',array(
                    'choices' => $this->getIssueLabelChoices(),
                    'multiple' => true,   // Multiple selection allowed
                    'expanded' => true,   // Render as checkboxes
                    //'property' => 'title', // Assuming that the entity has a "name" property
                    //'class' => 'VersionContol\GitControlBundle\Entity\IssueLabel',
                    'required' => false,
                    'choices_as_values' => true,
                    'choice_label' => function($issueLabel) {
                        if($issueLabel){
                            return $issueLabel->getTitle();
                        }
                        return;
                    },
                    'choice_value' => function($issueLabel) {
                         if($issueLabel){
                           return $issueLabel->getId();
                         }return;
                       },
                    /*'query_builder' => function (EntityRepository $er) use ($project) {
                        return $er->createQueryBuilder('a')
                            ->where('a.project = :project OR a.allProjects = 1')
                            ->setParameter('project', $project)
                            ->orderBy('a.id', 'ASC');
                    },*/
                ))
        ;
    }
    
    protected function getIssueMilestoneChoices(){
        $issueMilestoneRepository = $this->issueManager->getIssueMilestoneRepository();
        return $issueMilestoneRepository->listMilestones();
    }
    
    protected function getIssueLabelChoices(){
        $issueLabelRepository = $this->issueManager->getIssueLabelRepository();
        return $issueLabelRepository->listLabels();

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionContol\GitControlBundle\Entity\Issue'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontol_gitcontrolbundle_issue';
    }
}
