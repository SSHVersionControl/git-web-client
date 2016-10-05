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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserProjectsType extends AbstractType
{
    
    /**
     * Project Id. Used in query to select all user not apart of this project already
     * 
     * @var integer 
     */
    protected $projectId;
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $projectId = $this->getProjectId();
        $builder
            ->add('roles',ChoiceType::class, array(
                    'label' => 'User Role'
                    ,'choices'  => array('Reporter' => 'Reporter', 'Developer' => 'Developer', 'Master' => 'Master')
                    ,'required' => false
                    ,'empty_value' => 'Please select a role'
                ))
            ->add('user',EntityType::class, array(
                    'class' => 'VersionControl\GitControlBundle\Entity\User\User',
                    'choice_label' => 'username',
                    'empty_value' => 'Please select a user',
                    'query_builder' => function(EntityRepository $er) use($projectId) {
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
            ->add('project', 'hidden_entity',array(
                    'class' => 'VersionControl\GitControlBundle\Entity\Project'
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VersionControl\GitControlBundle\Entity\UserProjects'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'versioncontrol_gitcontrolbundle_userprojects';
    }
    
    /**
     * Gets the project Id
     * @return integer
     */
    public function getProjectId() {
        return $this->projectId;
    }

    /**
     * Sets the project Id
     * @param integer $projectId
     * @return \VersionControl\GitControlBundle\Form\UserProjectsType
     */
    public function setProjectId($projectId) {
        $this->projectId = $projectId;
        return $this;
    }


}
