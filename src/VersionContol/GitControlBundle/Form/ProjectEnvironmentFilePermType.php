<?php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectEnvironmentFilePermType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileOwner')
            ->add('fileGroup')
            ->add('enableFilePermissions','checkbox', array(
                'label'    => 'Update file permissions when git alters files?',
                'required' => false,
                ))
            ->add('permissionOwnerRead','checkbox', array(
                'label'    => 'Read',
                'required' => false,
                ))
            ->add('permissionOwnerWrite','checkbox', array(
                'label'    => 'Write',
                'required' => false,
                ))
            ->add('permissionOwnerExecute','checkbox', array(
                'label'    => 'Execute',
                'required' => false,
                ))
            ->add('permissionStickyUid','checkbox', array(
                'label'    => 'Set UID',
                'required' => false,
                ))
                
            ->add('permissionGroupRead','checkbox', array(
                'label'    => 'Read',
                'required' => false,
                ))
            ->add('permissionGroupWrite','checkbox', array(
                'label'    => 'Write',
                'required' => false,
                ))
            ->add('permissionGroupExecute','checkbox', array(
                'label'    => 'Execute',
                'required' => false,
                ))
            ->add('permissionStickyGid','checkbox', array(
                'label'    => 'Set GID',
                'required' => false,
                ))
                
            ->add('permissionOtherRead','checkbox', array(
                'label'    => 'Read',
                'required' => false,
                ))
            ->add('permissionOtherWrite','checkbox', array(
                'label'    => 'Write',
                'required' => false,
                ))
            ->add('permissionOtherExecute','checkbox', array(
                'label'    => 'Execute',
                'required' => false,
                ))
            ->add('permissionStickyBit','checkbox', array(
                'label'    => 'Set UID',
                'required' => false,
                ))
                
            ->add('fileMode')
            
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionContol\GitControlBundle\Entity\ProjectEnvironmentFilePerm'
            ,'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontol_gitcontrolbundle_projectenvironmentfileperm';
    }
}
