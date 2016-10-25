<?php
// src/VersionControl/GitControlBundle/Form/RegistrationType.php
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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use FOS\UserBundle\Form\Type\ProfileFormType;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')

                ->add('admin', CheckboxType::class, array(
                'label' => 'Admin',
                'required' => false,
                ));
    }

    public function getParent()
    {
        return ProfileFormType::class;
    }

    public function getBlockPrefix()
    {
        return 'version_control_user_edit';
    }
}
