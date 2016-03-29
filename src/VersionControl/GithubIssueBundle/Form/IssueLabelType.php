<?php

namespace VersionControl\GithubIssueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IssueLabelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('hexColor')
            //->add('project', 'hidden_entity',array(
            //        'class' => 'VersionControl\GitControlBundle\Entity\Project'
            //    ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GithubIssueBundle\Entity\Issues\IssueLabel'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontrol_gitcontrolbundle_issuelabel';
    }
}
