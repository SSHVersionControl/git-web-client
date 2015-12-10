<?php
// src/VersionContol/GitControlBundle/Validator/Constraints/NoProfanity.php
namespace VersionContol\GitControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StatusHash extends Constraint
{
    public $message = 'validate.constraint.StatusHash';
    
    public function validatedBy()
    {
        return 'status_hash_validator';
    }
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}

?>

