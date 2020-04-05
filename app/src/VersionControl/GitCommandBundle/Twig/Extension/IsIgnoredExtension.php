<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Twig\Extension;

use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;
use VersionControl\GitCommandBundle\GitCommands\GitCommand;

/**
 * Twig extension providing filter to get current project environment for project.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class IsIgnoredExtension extends AbstractExtension
{
    /**
     * @var GitCommand
     */
    protected $gitCommand;

    public function __construct(GitCommand $gitCommand)
    {
        $this->gitCommand = $gitCommand;
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'versioncommand_isignored';
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array(
            new TwigTest('isIgnored', array($this, 'isFileIgnored')),
        );
    }

    /**
     * @param $filePath
     *
     * @return string
     * @throws RunGitCommandException
     * @throws RuntimeException
     */
    public function isFileIgnored($filePath): string
    {
        return $this->gitCommand->command('files')->isFileIgnored($filePath);
    }
}
