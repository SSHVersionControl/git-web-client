<?php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            //->add('dueOn')
            ->add('dueOn', 'datetime', array('date_widget' => "single_text", 'time_widget' => "single_text"))

            ->add('project', 'hidden_entity',array(
                    'class' => 'VersionContol\GitControlBundle\Entity\Project'
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
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
