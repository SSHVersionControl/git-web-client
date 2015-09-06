<?php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IssueType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                    'class' => 'VersionContol\GitControlBundle\Entity\IssueMilestone'
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
                    'required' => false
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
