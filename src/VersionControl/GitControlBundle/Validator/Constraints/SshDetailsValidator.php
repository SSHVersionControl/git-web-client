<?php
// src/VersionControl/GitControlBundle/Validator/Constraints/SshDetailsValidator.php
namespace VersionControl\GitControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use VersionControl\GitCommandBundle\Service\SshProcess;

use phpseclib\Net\SFTP;

class SshDetailsValidator extends ConstraintValidator
{
    /**
     *
     * @var VersionControl\GitCommandBundle\Service\SshProcess 
     */
    public $sshProcess;
    
    public function __construct(SshProcess $sshProcess) {
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
                if (!$sftp->login($projectEnvironment->getUsername(), $projectEnvironment->getPassword())) {
                    $this->context->buildViolation($constraint->message)
                        ->atPath('title')
                        ->addViolation();
                }else{
                    //Validate path
                     if ($sftp->is_dir($gitPath) === false){
                        $this->context->buildViolation($constraint->messageFileDoesNotExist)
                            ->atPath('path')
                            ->addViolation();
                    }
                }
            }catch(\Exception $e){
                $this->context->buildViolation($e->getMessage())
                        ->atPath('title')
                        ->addViolation();
            }
        }
    }
}