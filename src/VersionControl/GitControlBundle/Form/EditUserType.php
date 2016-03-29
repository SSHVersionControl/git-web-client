<?php
// src/VersionControl/GitControlBundle/Form/RegistrationType.php

namespace VersionControl\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')

                ->add('admin', 'checkbox', array(
                'label'    => 'Admin',
                'required' => false,
                ));
    }

    public function getParent()
    {
        return 'fos_user_profile';
    }

    public function getName()
    {
        return 'version_control_user_edit';
    }
}