<?php
// src/VersionControl/GitControlBundle/Validator/Constraints/SshDetails.php

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
class SshDetails extends Constraint
{
    public $message = 'validate.constraint.SshDetails';
    
    public $messageFileDoesNotExist = 'validate.constraint.SshDetails.FileDoesNotExist';
    
    public function validatedBy()
    {
        return 'ssh_details_validator';
    }
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}

?>
