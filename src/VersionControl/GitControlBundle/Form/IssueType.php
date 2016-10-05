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
use VersionControl\GitControlBundle\Repository\IssueMilestoneRepository;
//use VersionControl\GitControlBundle\Repository\IssueLabelRepository;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;

class IssueType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $project = $builder->getData()->getProject();
        $builder
            ->add('title')
            ->add('description')
            //->add('status')
            //->add('closedAt')
            //->add('createdAt')
            //->add('updatedAt')
            //->add('githubNumber')
            ->add('issueMilestone',EntityType::class,array(
                    'multiple' => false,   // Multiple selection allowed
                    //'expanded' => true,   // Render as checkboxes
                    'placeholder' => 'Choose a milestone',
                    'required' => false,
                    'property' => 'title', // Assuming that the entity has a "name" property
                    'class' => 'VersionControl\GitControlBundle\Entity\IssueMilestone',
                    'query_builder' => function (IssueMilestoneRepository $er) use ($project) {
                        return $er->createQueryBuilder('a')
                            ->where('a.project = :project')
                            ->setParameter('project', $project)
                            ->orderBy('a.id', 'ASC');
                    },
                ))
            ->add('project', 'hidden_entity',array(
                    'class' => 'VersionControl\GitControlBundle\Entity\Project'
                ))
            //->add('verUser')
            ->add('issueLabel',EntityType::class,array(
                    'multiple' => true,   // Multiple selection allowed
                    'expanded' => true,   // Render as checkboxes
                    'property' => 'title', // Assuming that the entity has a "name" property
                    'class' => 'VersionControl\GitControlBundle\Entity\IssueLabel',
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) use ($project) {
                        return $er->createQueryBuilder('a')
                            ->where('a.project = :project OR a.allProjects = 1')
                            ->setParameter('project', $project)
                            ->orderBy('a.id', 'ASC');
                    },
                ))
        ;
    }
    
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitControlBundle\Entity\Issue'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'versioncontrol_gitcontrolbundle_issue';
    }
}