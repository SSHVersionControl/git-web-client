<?php
namespace VersionControl\GitControlBundle\Form\Field;
use Symfony\Component\Form\AbstractType;
use Dpn\ToolsBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;

class EntityHiddenType extends AbstractType
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new EntityToIdTransformer($this->objectManager, $options['class']);
        $builder->addModelTransformer($transformer);
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('class'))
            ->setDefaults(array(
                'invalid_message' => 'The entity does not exist.',
            ))
        ;
    }
    
    public function getParent()
    {
        return 'hidden';
    }
    
    public function getName()
    {
        return 'entity_hidden';
    }
}