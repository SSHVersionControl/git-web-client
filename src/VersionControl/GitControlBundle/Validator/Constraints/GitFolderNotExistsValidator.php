<?php
// src/VersionControl/GitControlBundle/Validator/Constraints/GitFolderNotExistsValidator.php

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
use Symfony\Component\Validator\ConstraintValidator;
use VersionControl\GitCommandBundle\Service\SftpProcessInterface;
use VersionControl\GitCommandBundle\GitCommands\Exception\SshLoginException;

class GitFolderNotExistsValidator extends ConstraintValidator
{
    /**
     * @var VersionControl\GitCommandBundle\Service\SftpProcessInterface
     */
    public $sftpProcess;

    public function __construct(SftpProcessInterface $sftpProcess)
    {
        $this->sftpProcess = $sftpProcess;
    }

    /**
     * Validates Project Enviroment.
     *
     * @param VersionControl\GitControlBundle\Entity\ProjectEnvironment $projectEnvironment
     * @param Constraint                                                $constraint
     */
    public function validate($projectEnvironment, Constraint $constraint)
    {
        $gitPath = rtrim(trim($projectEnvironment->getPath()), '/');
        if ($projectEnvironment->getSsh() === true) {
            $this->sftpProcess->setGitEnviroment($projectEnvironment);
            try {
                if ($this->sftpProcess->fileExists($gitPath.'/.git') === true) {
                    $this->context->buildViolation($constraint->message)
                            ->atPath('path')
                            ->addViolation();
                }
            } catch (SshLoginException $sshLoginException) {
                $this->context->buildViolation($sshLoginException->getMessage())
                        ->atPath('path')
                        ->addViolation();
            } catch (Exception $ex) {
                $this->context->buildViolation($ex->getMessage())
                        ->atPath('path')
                        ->addViolation();
            }
        } else {
            if (file_exists($gitPath.'/.git') === true) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('path')
                    ->addViolation();
            }
        }
    }
}
