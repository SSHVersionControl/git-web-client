<?php
// src/VersionContol/GitControlBundle/Form/RegistrationType.php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');

                /*->add('admin', 'checkbox', array(
                'label'    => 'Admin',
                'required' => false,
                ));*/
    }

    public function getParent()
    {
        return 'fos_user_registration';
    }

    public function getName()
    {
        return 'version_control_user_registration';
    }
}