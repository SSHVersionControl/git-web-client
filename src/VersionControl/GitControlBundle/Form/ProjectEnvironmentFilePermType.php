<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ProjectEnvironmentFilePermType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileOwner')
            ->add('fileGroup')
            ->add('enableFilePermissions', CheckboxType::class, array(
                'label' => 'Update file permissions when git alters files?',
                'required' => false,
                ))
            ->add('permissionOwnerRead', CheckboxType::class, array(
                'label' => 'Read',
                'required' => false,
                ))
            ->add('permissionOwnerWrite', CheckboxType::class, array(
                'label' => 'Write',
                'required' => false,
                ))
            ->add('permissionOwnerExecute', CheckboxType::class, array(
                'label' => 'Execute',
                'required' => false,
                ))
            ->add('permissionStickyUid', CheckboxType::class, array(
                'label' => 'Set UID',
                'required' => false,
                ))

            ->add('permissionGroupRead', CheckboxType::class, array(
                'label' => 'Read',
                'required' => false,
                ))
            ->add('permissionGroupWrite', CheckboxType::class, array(
                'label' => 'Write',
                'required' => false,
                ))
            ->add('permissionGroupExecute', CheckboxType::class, array(
                'label' => 'Execute',
                'required' => false,
                ))
            ->add('permissionStickyGid', CheckboxType::class, array(
                'label' => 'Set GID',
                'required' => false,
                ))

            ->add('permissionOtherRead', CheckboxType::class, array(
                'label' => 'Read',
                'required' => false,
                ))
            ->add('permissionOtherWrite', CheckboxType::class, array(
                'label' => 'Write',
                'required' => false,
                ))
            ->add('permissionOtherExecute', CheckboxType::class, array(
                'label' => 'Execute',
                'required' => false,
                ))
            ->add('permissionStickyBit', CheckboxType::class, array(
                'label' => 'Set UID',
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
            'data_class' => 'VersionControl\GitControlBundle\Entity\ProjectEnvironmentFilePerm',
            //,'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'versioncontrol_gitcontrolbundle_projectenvironmentfileperm';
    }
}
