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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use VersionControl\GitControlBundle\Entity\Commit;

class CommitType extends AbstractType
{
    protected $fileChoices = array();

    protected $includeIssues;

    protected $gitRemoteVersions;

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->includeIssues = $options['includeIssues'];
        $this->gitRemoteVersions = $options['gitRemoteVersions'];
        $this->fileChoices = $options['fileChoices'];

        $builder
            ->add('comment', TextareaType::class, array(
            'label' => 'Comment', 'required' => false,
            //,'constraints' => array(
               // new NotBlank(array('message'=>'Please add a commit comment.'))
            //)
            ))
        ->add('statushash', HiddenType::class, array(
            //'data' => $this->gitCommands->getStatusHash(),
            //'constraints' => array(
                //new NotBlank()
            //)
            ));
        if (count($this->getFileChoices()) > 0) {
            $builder->add('files', ChoiceType::class, array(
                'choices' => $this->getFileChoices(),
                //'class' => '\VersionControl\GitControlBundle\Entity\GitFile',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices_as_values' => true,
                'choice_label' => function ($gitFile) {
                    return $gitFile->getPath1();
                },
                 'choice_value' => function ($gitFile) {
                     return $gitFile->getPath1();
                 },
                //'constraints' => array(
                //    new NotBlank()
                //    ,new \VersionControl\GitControlBundle\Validator\Constraints\StatusHash()
                //)
                ));
        } else {
            $builder->add('files', CheckboxType::class, array(
                'label' => 'Commit all files',
                'required' => false,
            ));
        }

        if ($this->includeIssues === true) {
            $builder->add('issue', HiddenType::class)
            ->add('issueAction', ChoiceType::class, [
                    'choices' => [
                        'Close Issue' => [
                            'Fixed Issue' => 'Fixed',
                            'Closed Issue' => 'Closed',
                            'Resolved Issue' => 'Resolved',
                        ],
                        'Related to Issue' => [
                            'Reference Issue' => 'Reference',
                            'See Issue' => 'See',
                        ],
                    ],
                    'choices_as_values' => true,
                ]
                );
        }

        if (is_array($this->gitRemoteVersions) && count($this->gitRemoteVersions) > 0) {
            $remoteChoices = array();
            foreach ($this->gitRemoteVersions as $remoteVersion) {
                $remoteChoices[$remoteVersion[0].'('.$remoteVersion[1].')'] = $remoteVersion[0];
            }
            $builder->add('pushRemote', ChoiceType::class, [
                'choices' => $remoteChoices,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices_as_values' => true,
                'label' => 'Push changes immediately to:',

            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Commit::class,
            'includeIssues' => false,
            'gitRemoteVersions' => array(),
            'fileChoices' => array(),
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'versioncontrol_gitcontrolbundle_commit';
    }

    public function getFileChoices()
    {
        if (count($this->fileChoices) > 200) {
            return array();
        }

        return $this->fileChoices;
    }

    public function setFileChoices($fileChoices)
    {
        $this->fileChoices = $fileChoices;

        return $this;
    }
}
