<?php
/*
 * This file is part of the GithubIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GithubIssueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IssueType extends AbstractType
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
        
        //$project = $builder->getData()->getProject();
        $builder
            ->add('title')
            ->add('description')
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
                    //'class' => 'VersionControl\GitControlBundle\Entity\IssueMilestone',
                    /*'query_builder' => function (IssueMilestoneRepository $er) use ($project) {
                        return $er->createQueryBuilder('a')
                            ->where('a.project = :project')
                            ->setParameter('project', $project)
                            ->orderBy('a.id', 'ASC');
                    },*/
                ))
            //->add('project', 'hidden_entity',array(
             //       'class' => 'VersionControl\GitControlBundle\Entity\Project'
             //   ))
            //->add('verUser')
            ->add('issueLabel','choice',array(
                    'choices' => $this->getIssueLabelChoices(),
                    'multiple' => true,   // Multiple selection allowed
                    'expanded' => true,   // Render as checkboxes
                    //'property' => 'title', // Assuming that the entity has a "name" property
                    //'class' => 'VersionControl\GitControlBundle\Entity\IssueLabel',
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
            'data_class' => 'VersionControl\GitControlBundle\Entity\Issues\Issue'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontrol_gitcontrolbundle_issue';
    }
}
