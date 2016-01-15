<?php
// src/VersionContol/GitControlBundle/Validator/Constraints/NoProfanity.php
namespace VersionContol\GitControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GitFolderExists extends Constraint
{
    public $message = 'validate.constraint.GitFolderExists.error';
    
    public function validatedBy()
    {
        return 'git_folder_exists_validator';
    }
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}

?>
