<?php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IssueMilestoneType extends AbstractType
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
            ->add('state')
            ->add('dueOn')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('closedAt')
            ->add('verUser')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionContol\GitControlBundle\Entity\IssueMilestone'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontol_gitcontrolbundle_issuemilestone';
    }
}
