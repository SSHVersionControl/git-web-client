<?php
// src/VersionContol/GitControlBundle/Validator/Constraints/SshDetailsValidator.php
namespace VersionContol\GitControlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use VersionContol\GitControlBundle\Utility\SshProcess;


class GitFolderValidator extends ConstraintValidator
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
        $gitPath = rtrim(trim($projectEnvironment->getPath()),'/');
        if($projectEnvironment->getSsh() === true){
           
            $sftp = new SFTP($projectEnvironment->getHost(), 22);
            try{
                if ($sftp->login($projectEnvironment->getUsername(), $projectEnvironment->getPassword())) {
                    if ($sftp->file_exists($gitPath.'/.git') === false){
                        $this->context->buildViolation($constraint->message)
                            ->atPath('title')
                            ->addViolation();
                    }
                }
            }catch(\Exception $e){
                $this->context->buildViolation($e->getMessage())
                        ->atPath('title')
                        ->addViolation();
            }
        }else{
            if (file_exists($gitPath.'/.git') === false){
                $this->context->buildViolation($constraint->message)
                    ->atPath('title')
                    ->addViolation();
            }
        }
    }
}