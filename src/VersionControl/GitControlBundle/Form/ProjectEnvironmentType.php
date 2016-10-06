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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
            ->add('ssh',CheckboxType::class, array(
                'label'    => 'Use SSH?',
                'required' => false,
                ))
            ->add('host')
            ->add('username')
            ->add('password',PasswordType::class,array('required' => false))
            ->add('privateKey',TextareaType::class,array('required' => false))
            ->add('privateKeyPassword',PasswordType::class,array('required' => false))
            ->add('projectEnvironmentFilePerm',  new ProjectEnvironmentFilePermType(), array('required'  => false));
        
        if($this->useCloneLocation === true){
            $builder->add('gitCloneLocation',TextType::class,array('required' => false)) ; 
        }
        
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitControlBundle\Entity\ProjectEnvironment'
            //,'cascade_validation' => true
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
    public function getBlockPrefix()
    {
        return 'versioncontrol_gitcontrolbundle_projectenvironment';
    }
}
