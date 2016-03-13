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
    
    protected $gitRemoteVersions;
    
    public function __construct($includeIssues = false,$gitRemoteVersions = array()) {
        $this->includeIssues = $includeIssues;
        $this->gitRemoteVersions = $gitRemoteVersions;
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
            ));
        if(count($this->getFileChoices()) > 0){
            $builder->add('files', 'choice', array(
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
        }else{
            $builder->add('files','checkbox', array(
                'label'    => 'Commit all files',
                'required' => false,
            )); 
        }
 
                
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
        
        if(is_array($this->gitRemoteVersions) && count($this->gitRemoteVersions) > 0){
            $remoteChoices = array();
            foreach($this->gitRemoteVersions as $remoteVersion){
                $remoteChoices[$remoteVersion[0]] = $remoteVersion[0].'('.$remoteVersion[1].')'; 
            }
            $builder->add('pushRemote','choice', [
                'choices' => $remoteChoices,
                'multiple'     => true,
                'expanded'  => true,
                'required'  => false,
                'choices_as_values' => false,
                'label' => 'Push changes immediately to:'
                
            ]);
            
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

        if(count($this->fileChoices) > 200){
            return array();
        }
        
        return $this->fileChoices;
    }

    public function setFileChoices($fileChoices) {
        $this->fileChoices = $fileChoices;
        return $this;
    }


}