<?php
// src/VersionControl/GitControlBundle/Validator/Constraints/SshDetailsValidator.php

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

class SshDetailsValidator extends ConstraintValidator
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
                if ($this->sftpProcess->isDir($gitPath) === false) {
                    $this->context->buildViolation('This directory (%gitPath%) does not exist. Please check that you have entered the correct path in %projectEnviromentTitle%')
                        ->setParameter('%gitPath%', $gitPath)
                        ->setParameter('%projectEnviromentTitle%', $projectEnvironment->getTitle())
                        ->atPath('path')
                        ->addViolation();
                }
            } catch (SshLoginException $sshLoginException) {
                $this->context->buildViolation($sshLoginException->message)
                        ->atPath('title')
                        ->addViolation();
            } catch (\Exception $ex) {
                $this->context->buildViolation($constraint->message)

                        ->atPath('title')
                        ->addViolation();
            }
        }
    }
}
