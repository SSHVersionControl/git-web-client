<?php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectIssueIntegratorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('repoType','choice', [
                    'choices' => [
                        'Github' => 'Github',
                        'GitLab' => 'GitLab',
                        'BitBucket' => 'BitBucket',
                    ],
                    'choices_as_values' => true
                ]
            )
            ->add('repoName')
            ->add('ownerName')
            ->add('apiToken')
            ->add('url')
            ->add('project', 'hidden_entity',array(
                    'class' => 'VersionContol\GitControlBundle\Entity\Project'
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionContol\GitControlBundle\Entity\ProjectIssueIntegrator'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontol_gitcontrolbundle_projectissueintegrator';
    }
}
