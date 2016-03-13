<?php
namespace VersionControl\GitlabIssueBundle\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use VersionControl\GitlabIssueBundle\Form\DataTransformer\IdToGitlabProjectTransformer;

class GitlabProjectType extends AbstractType
{

    
    public function __construct()
    {
        
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new IdToGitlabProjectTransformer());
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            //->setRequired(array('class'))
            ->setDefaults(array(
                'invalid_message' => 'The entity does not exist.',
            ))
        ;
    }
    
    public function getParent()
    {
        return 'choice';
    }
    
    public function getName()
    {
        return 'gitlab_project_choice';
    }
}