<?php

namespace VersionControl\GitControlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IssueCommentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment')
            ->add('issue', 'hidden_entity',array(
                    'class' => 'VersionControl\GitControlBundle\Entity\Issue'
                ))

        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitControlBundle\Entity\IssueComment'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontrol_gitcontrolbundle_issuecomment';
    }
}
