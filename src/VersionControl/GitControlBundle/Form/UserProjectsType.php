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
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Lrotherfield\Component\Form\Type\HiddenEntityType;

class UserProjectsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $projectId = $options['projectId'];

        $builder
            ->add('roles', ChoiceType::class, array(
                    'label' => 'User Role', 'choices' => array('Reporter' => 'Reporter', 'Developer' => 'Developer', 'Master' => 'Master'), 'required' => false, 'placeholder' => 'Please select a role', 'choices_as_values' => true,
                ))
            ->add('user', EntityType::class, array(
                    'class' => 'VersionControl\GitControlBundle\Entity\User\User',
                    'choice_label' => 'username',
                    'placeholder' => 'Please select a user',
                    'query_builder' => function (EntityRepository $er) use ($projectId) {
                        $qb = $er->createQueryBuilder('a');

                        $nots = $qb
                                ->join('VersionControlGitControlBundle:UserProjects', 'b')
                                ->where('a.id = b.user AND b.project = :id')
                                ->setParameter('id', $projectId)
                                ->getQuery()
                                ->getResult();

                        return $er->createQueryBuilder('u')
                            ->where($qb->expr()->notIn('u.username', $nots))
                            ->orderBy('u.username', 'ASC');
                    },
                ))
            ->add('project', HiddenEntityType::class, array(
                    'class' => 'VersionControl\GitControlBundle\Entity\Project',
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitControlBundle\Entity\UserProjects',
            'projectId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'versioncontrol_gitcontrolbundle_userprojects';
    }
}
