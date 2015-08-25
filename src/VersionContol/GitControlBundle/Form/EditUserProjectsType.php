<?php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class EditUserProjectsType extends AbstractType
{
    

    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('roles','choice', array(
                    'label' => 'User Role'
                    ,'choices'  => array('Reporter' => 'Reporter', 'Developer' => 'Developer', 'Master' => 'Master')
                    ,'required' => false
                    ,'empty_value' => 'Please select a role'
                ))
            ->add('project', 'hidden_entity',array(
                    'class' => 'VersionContol\GitControlBundle\Entity\Project'
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionContol\GitControlBundle\Entity\UserProjects'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontol_gitcontrolbundle_userprojects';
    }
    
}
