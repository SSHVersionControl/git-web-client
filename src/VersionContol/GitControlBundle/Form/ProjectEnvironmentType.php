<?php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;

class ProjectEnvironmentType extends AbstractType
{
    protected $useCloneLocation;
    
    public function __construct($useCloneLocation = false) {
        $this->useCloneLocation = $useCloneLocation;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('path')
            ->add('ssh','checkbox', array(
                'label'    => 'Use SSH?',
                'required' => false,
                ))
            ->add('host')
            ->add('username')
            ->add('password','password',array('required' => false))
            ->add('projectEnvironmentFilePerm',  new ProjectEnvironmentFilePermType(), array('required'  => false));
        
        if($this->useCloneLocation === true){
            $builder->add('gitCloneLocation','text',array('required' => false)) ; 
        }
        
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionContol\GitControlBundle\Entity\ProjectEnvironment'
            ,'cascade_validation' => true
            ,'validation_groups' => function (FormInterface $form) {
                
                if($form->has('gitaction')){
                    $gitAction =  $form->get('gitaction')->getData();
                    
                    if ($gitAction == 'new') {
                        return array('Default', 'NewGit');
                    }elseif ($gitAction == 'clone') {
                        return array('Default', 'CloneGit');
                    }elseif ($gitAction == 'existing') {
                        return array('Default', 'ExistingGit');
                    }
                }

                return array('Default', 'ExistingGit');
            },
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontol_gitcontrolbundle_projectenvironment';
    }
}
