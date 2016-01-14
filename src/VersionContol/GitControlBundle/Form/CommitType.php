<?php

namespace VersionContol\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CommitType extends AbstractType
{
    protected $fileChoices = array();
    
    protected $includeIssues;
    
    public function __construct($includeIssues = false) {
        $this->includeIssues = $includeIssues;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder     
            ->add('comment', 'textarea', array(
            'label' => 'Comment'
            ,'required' => false
            //,'constraints' => array(
               // new NotBlank(array('message'=>'Please add a commit comment.'))
            //)
            ))
        ->add('statushash', 'hidden', array(
            //'data' => $this->gitCommands->getStatusHash(),
            //'constraints' => array(
                //new NotBlank()
            //)
            ))

        ->add('files', 'choice', array(
            'choices' => $this->getFileChoices(),
            //'class' => '\VersionContol\GitControlBundle\Entity\GitFile',
            'multiple'     => true,
            'expanded'  => true,
            'required'  => false,
            'choices_as_values' => true,
            'choice_label' => function($gitFile) {
                    return $gitFile->getPath1();
                },
             'choice_value' => function($gitFile) {
                    return $gitFile->getPath1();
                },
            //'constraints' => array(
            //    new NotBlank()
            //    ,new \VersionContol\GitControlBundle\Validator\Constraints\StatusHash()
            //)
            ));
                
        if($this->includeIssues === true){
            $builder->add('issue', 'hidden')
            ->add('issueAction','choice', [
                    'choices' => [
                        'Close Issue' => [
                            'Fixed Issue' => 'Fixed',
                            'Closed Issue' => 'Closed',
                            'Resolved Issue' => 'Resolved',
                        ],
                        'Related to Issue' => [
                            'Reference Issue' => 'Reference',
                            'See Issue' => 'See'
                        ]
                    ],
                    'choices_as_values' => true
                ]
                );
        }
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionContol\GitControlBundle\Entity\Commit'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontol_gitcontrolbundle_commit';
    }
    
    public function getFileChoices() {
        return $this->fileChoices;
    }

    public function setFileChoices($fileChoices) {
        $this->fileChoices = $fileChoices;
        return $this;
    }


}