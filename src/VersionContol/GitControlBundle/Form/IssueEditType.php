<?php

namespace VersionContol\GitControlBundle\Form;

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
            ->add('issueMilestone','entity')
            ->add('project', 'hidden_entity',array(
                    'class' => 'VersionContol\GitControlBundle\Entity\Project'
                ))
            ->add('issueLabel')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionContol\GitControlBundle\Entity\Issue'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontol_gitcontrolbundle_issue';
    }
}
