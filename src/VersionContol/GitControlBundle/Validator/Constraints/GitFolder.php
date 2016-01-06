<?php
// src/VersionContol/GitControlBundle/Validator/Constraints/NoProfanity.php
namespace VersionContol\GitControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GitFolder extends Constraint
{
    public $message = 'validate.constraint.GitFolder';
    
    public function validatedBy()
    {
        return 'git_folder_validator';
    }
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}

?>
