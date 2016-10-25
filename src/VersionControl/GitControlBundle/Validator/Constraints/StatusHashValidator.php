<?php
// src/VersionControl/GitControlBundle/Validator/Constraints/StatusHashValidator.php

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
use VersionControl\GitCommandBundle\GitCommands\GitCommand;
use VersionControl\GitControlBundle\Utility\ProjectEnvironmentStorage;

class StatusHashValidator extends ConstraintValidator
{
    /**
     * @var VersionControl\GitCommandBundle\GitCommands\Command\GitStatusCommand
     */
    public $gitStatusCommand;

    public $projectEnvironmentStorage;

    public function __construct(GitCommand $gitCommand, ProjectEnvironmentStorage $projectEnvironmentStorage)
    {
        $this->gitStatusCommand = $gitCommand->command('status');
        $this->projectEnvironmentStorage = $projectEnvironmentStorage;
    }

    public function validate($commitEntity, Constraint $constraint)
    {
        $statusHash = $commitEntity->getStatusHash();

        $this->projectEnvironment = $this->projectEnvironmentStorage->getProjectEnviromment($commitEntity->getProject());

        $this->gitStatusCommand->overRideGitEnvironment($this->projectEnvironment);

        $currentStatusHash = $this->gitStatusCommand->getStatusHash();

        if ($currentStatusHash !== $statusHash) {
            $this->context->buildViolation($constraint->getMessage())
            ->setParameter('{{statushash}}', $statusHash)
            ->setParameter('{{currentstatushash}}', $currentStatusHash)
            ->atPath('files')
            ->addViolation();
        }
    }
}
