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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IssueEditType extends AbstractType
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
            ->add('status','choice',array('label' => 'State'
                    ,'choices'  => array('open' => 'Open', 'closed' => 'Close')
                    ,'required' => false
                    ,'empty_value' => 'Please select a State')
                    )
            ->add('issueMilestone','entity',array(
                    'multiple' => false,   // Multiple selection allowed
                    //'expanded' => true,   // Render as checkboxes
                    'placeholder' => 'Choose a milestone',
                    'required' => false,
                    'property' => 'title', // Assuming that the entity has a "name" property
                    'class' => 'VersionControl\GitControlBundle\Entity\IssueMilestone'
                ))
             ->add('issueLabel','entity',array(
                    'multiple' => true,   // Multiple selection allowed
                    'expanded' => true,   // Render as checkboxes
                    'property' => 'title', // Assuming that the entity has a "name" property
                    'class' => 'VersionControl\GitControlBundle\Entity\IssueLabel',
                    'required' => false
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitControlBundle\Entity\Issue'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontrol_gitcontrolbundle_issue';
    }
}
