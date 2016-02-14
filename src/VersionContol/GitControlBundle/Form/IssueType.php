<?php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use VersionContol\GitControlBundle\Repository\IssueMilestoneRepository;
//use VersionContol\GitControlBundle\Repository\IssueLabelRepository;
use Doctrine\ORM\EntityRepository;

class IssueType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $project = $builder->getData()->getProject();
        $builder
            ->add('title')
            ->add('description')
            //->add('status')
            //->add('closedAt')
            //->add('createdAt')
            //->add('updatedAt')
            //->add('githubNumber')
            ->add('issueMilestone','entity',array(
                    'multiple' => false,   // Multiple selection allowed
                    //'expanded' => true,   // Render as checkboxes
                    'placeholder' => 'Choose a milestone',
                    'required' => false,
                    'property' => 'title', // Assuming that the entity has a "name" property
                    'class' => 'VersionContol\GitControlBundle\Entity\IssueMilestone',
                    'query_builder' => function (IssueMilestoneRepository $er) use ($project) {
                        return $er->createQueryBuilder('a')
                            ->where('a.project = :project')
                            ->setParameter('project', $project)
                            ->orderBy('a.id', 'ASC');
                    },
                ))
            ->add('project', 'hidden_entity',array(
                    'class' => 'VersionContol\GitControlBundle\Entity\Project'
                ))
            //->add('verUser')
            ->add('issueLabel','entity',array(
                    'multiple' => true,   // Multiple selection allowed
                    'expanded' => true,   // Render as checkboxes
                    'property' => 'title', // Assuming that the entity has a "name" property
                    'class' => 'VersionContol\GitControlBundle\Entity\IssueLabel',
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) use ($project) {
                        return $er->createQueryBuilder('a')
                            ->where('a.project = :project OR a.allProjects = 1')
                            ->setParameter('project', $project)
                            ->orderBy('a.id', 'ASC');
                    },
                ))
        ;
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