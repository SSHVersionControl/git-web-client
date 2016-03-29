<?php

namespace VersionControl\GithubIssueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectIssueIntegratorGithubType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('repoName')
            ->add('ownerName')
            ->add('apiToken')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GithubIssueBundle\Entity\ProjectIssueIntegratorGithub'
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
