<?php
// src/VersionControl/GitControlBundle/Validator/Constraints/SshDetailsValidator.php
namespace VersionControl\GitControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use VersionControl\GitCommandBundle\Service\SshProcessInterface;

use phpseclib\Net\SFTP;

class GitFolderExistsValidator extends ConstraintValidator
{
    /**
     *
     * @var VersionControl\GitCommandBundle\Service\SshProcessInterface 
     */
    public $sshProcess;
    
    public function __construct(SshProcessInterface $sshProcess) {
        $this->sshProcess = $sshProcess;
    }
    
    /**
     * Validates Project Enviroment
     * 
     * @param VersionControl\GitControlBundle\Entity\ProjectEnvironment $projectEnvironment
     * @param Constraint $constraint
     */
    public function validate($projectEnvironment, Constraint $constraint)
    {
        $gitPath = rtrim(trim($projectEnvironment->getPath()),'/');
        if($projectEnvironment->getSsh() === true){
           
            $sftp = new SFTP($projectEnvironment->getHost(), 22);
            try{
                if ($sftp->login($projectEnvironment->getUsername(), $projectEnvironment->getPassword())) {
                    if ($sftp->file_exists($gitPath.'/.git') === false){
                        $this->context->buildViolation($constraint->message)
                            ->atPath('path')
                            ->addViolation();
                    }
                }
            }catch(\Exception $e){
                $this->context->buildViolation($e->getMessage())
                        ->atPath('path')
                        ->addViolation();
            }
        }else{
            if (file_exists($gitPath.'/.git') === false){
                $this->context->buildViolation($constraint->message)
                    ->atPath('path')
                    ->addViolation();
            }
        }
    }
}