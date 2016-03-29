<?php
// src/VersionControl/GitControlBundle/Validator/Constraints/NoProfanity.php
namespace VersionControl\GitControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GitFolderNotExists extends Constraint
{
    public $message = 'validate.constraint.GitFolderNotExists.error';
    
    public function validatedBy()
    {
        return 'git_folder_not_exists_validator';
    }
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}

?>
