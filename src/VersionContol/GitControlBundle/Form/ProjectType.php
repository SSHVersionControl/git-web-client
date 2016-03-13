<?php

namespace VersionContol\GitControlBundle\Form;

use VersionContol\GitControlBundle\Form\Embbed\ProjectEnvironmentEmbbedType;
use VersionContol\GitControlBundle\Form\ProjectEnvironmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
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
            /*->add('projectEnvironment', new ProjectEnvironmentEmbbedType, array(
                                'type' => new ProjectEnvironmentType(),
                                'allow_add'    => true,
                                //'prototype' => true,
                                'by_reference' => false,
                                'allow_delete' => true,
                                ))*/
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionContol\GitControlBundle\Entity\Project'
            ,'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontol_gitcontrolbundle_project';
    }
}
