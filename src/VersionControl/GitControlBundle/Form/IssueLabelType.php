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

class IssueLabelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('hexColor')
            ->add('project', 'hidden_entity',array(
                    'class' => 'VersionControl\GitControlBundle\Entity\Project'
                ))
        ;
    }
    
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitControlBundle\Entity\IssueLabel'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'versioncontrol_gitcontrolbundle_issuelabel';
    }
}
