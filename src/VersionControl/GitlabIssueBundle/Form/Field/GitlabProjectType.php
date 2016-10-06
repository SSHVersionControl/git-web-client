<?php
/*
 * This file is part of the GitlabIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitlabIssueBundle\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    
    public function configureOptions(OptionsResolver $resolver)
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
    
    public function getBlockPrefix()
    {
        return 'gitlab_project_choice';
    }
}