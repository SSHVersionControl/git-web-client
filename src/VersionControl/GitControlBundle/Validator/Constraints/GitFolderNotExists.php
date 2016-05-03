<?php
// src/VersionControl/GitControlBundle/Validator/Constraints/GitFolderNotExists.php

/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
