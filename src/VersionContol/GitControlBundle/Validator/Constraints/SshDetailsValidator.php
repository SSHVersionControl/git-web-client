<?php
// src/VersionContol/GitControlBundle/Validator/Constraints/SshDetailsValidator.php
namespace VersionContol\GitControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use VersionContol\GitControlBundle\Utility\SshProcess;

use phpseclib\Net\SFTP;

class SshDetailsValidator extends ConstraintValidator
{
    /**
     *
     * @var VersionContol\GitControlBundle\Utility\SshProcess 
     */
    public $sshProcess;
    
    public function __construct(SshProcess $sshProcess) {
        $this->sshProcess = $sshProcess;
    }
    
    /**
     * Validates Project Enviroment
     * 
     * @param VersionContol\GitControlBundle\Entity\ProjectEnvironment $projectEnvironment
     * @param Constraint $constraint
     */
    public function validate($projectEnvironment, Constraint $constraint)
    {
        if($projectEnvironment->getSsh() === true){
           
            $sftp = new SFTP($projectEnvironment->getHost(), 22);
            try{
                if (!$sftp->login($projectEnvironment->getUsername(), $projectEnvironment->getPassword())) {
                    $this->context->buildViolation($constraint->message)
                        ->atPath('title')
                        ->addViolation();
                }
            }catch(\Exception $e){
                $this->context->buildViolation($e->getMessage())
                        ->atPath('title')
                        ->addViolation();
            }
        }
    }
}