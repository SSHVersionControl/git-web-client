<?php
// src/VersionControl/GitControlBundle/Validator/Constraints/SshDetailsValidator.php
namespace VersionControl\GitControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use VersionControl\GitControlBundle\Utility\GitCommands\Command\GitStatusCommand;
use VersionControl\GitControlBundle\Utility\GitCommands\GitCommand;

class StatusHashValidator extends ConstraintValidator
{
    /**
     *
     * @var VersionControl\GitControlBundle\Utility\GitCommands\Command\GitStatusCommand 
     */
    public $gitStatusCommand;
    
    public function __construct(GitCommand $gitCommand) {
        $this->gitStatusCommand = $gitCommand->command('status');
    }
    
    public function validate($commitEntity, Constraint $constraint)
    {
     
        $statusHash = $commitEntity->getStatusHash();
        $this->gitStatusCommand->setProject($commitEntity->getProject());
        $currentStatusHash = $this->gitStatusCommand->getStatusHash();
        
        if($currentStatusHash !== $statusHash){
            $this->context->buildViolation($constraint->message)
            ->atPath('files')
            ->addViolation();  
        }
    }
}

