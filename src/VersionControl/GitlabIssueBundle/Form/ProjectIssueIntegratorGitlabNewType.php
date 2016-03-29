<?php

namespace VersionControl\GitlabIssueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectIssueIntegratorGitlabNewType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url')
            ->add('apiToken')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitlabIssueBundle\Entity\ProjectIssueIntegratorGitlab'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontrol_gitcontrolbundle_projectissueintegrator';
    }
}
