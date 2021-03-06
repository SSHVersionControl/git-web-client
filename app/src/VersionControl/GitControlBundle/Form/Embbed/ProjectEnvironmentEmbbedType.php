<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Form\Embbed;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Description of ProjectEnvironmentEmbbedType.
 *
 * @author paul
 */
class ProjectEnvironmentEmbbedType extends AbstractType
{
    //put your code here
    public function getBlockPrefix()
    {
        return 'projectenvironmentembbed';
    }

    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver  $resolver)
    {
        $resolver->setDefaults(array(
             //'data_class' => 'Lre\MetadataBundle\Entity\Curriculum\ResourceCurriculum',
            //'data_class' => NULL,
            //'cascade_validation' => true,
        ));
    }
}
