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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Lrotherfield\Component\Form\Type\HiddenEntityType;

class ProjectIssueIntegratorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('repoType',ChoiceType::class, [
                    'choices' => [
                        'Github' => 'Github',
                        'GitLab' => 'GitLab',
                        'BitBucket' => 'BitBucket',
                    ],
                    'choices_as_values' => true
                ]
            )
            ->add('repoName')
            ->add('ownerName')
            ->add('apiToken')
            ->add('url')
            ->add('project', HiddenEntityType::class,array(
                    'class' => 'VersionControl\GitControlBundle\Entity\Project'
                ))
        ;
    }
    
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitControlBundle\Entity\ProjectIssueIntegrator'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'versioncontrol_gitcontrolbundle_projectissueintegrator';
    }
}
