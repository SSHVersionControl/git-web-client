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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

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
            ->add('dueOn', DateTimeType::class, array('date_widget' => "single_text", 'time_widget' => "single_text" ,'required' => false,))

            ->add('project', 'hidden_entity',array(
                    'class' => 'VersionControl\GitControlBundle\Entity\Project'
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitControlBundle\Entity\IssueMilestone'
        ));
    }


    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'versioncontrol_gitcontrolbundle_issuemilestone';
    }
}
