<?php
// src/VersionControl/GitCommandBundle/Entity/GitDiffLine.php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Entity;

/**
 * Git Diff Line:.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitDiffLine
{
    public const NOCHANGE = 0;
    public const ADDED = 1;
    public const REMOVED = 2;

    /**
     * @var int
     */
    protected $type;

    /**
     * The line content.
     *
     * @var string
     */
    protected $line;

    /**
     * The line number. Can be a number or string eg '...'.
     *
     * @var string
     */
    protected $lineNumber;

    /**
     * Sets line and line type.
     *
     * @param string $line The line content
     */
    public function __construct($line)
    {
        $this->line = $line;

        $firstCharacter = $line[0] ?? '';

        if ($firstCharacter !== false) {
            if ($firstCharacter === '+') {
                $this->type = self::ADDED;
                // $type = Line::ADDED;
            } elseif ($firstCharacter === '-') {
                $this->type = self::REMOVED;
            } else {
                $this->type = self::NOCHANGE;
            }
        } else {
            $this->type = self::NOCHANGE;
        }
    }

    /**
     * Get Line type.
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Sets line type.
     *
     * @param int $type
     *
     * @return GitDiffLine
     */
    public function setType($type): GitDiffLine
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets line content.
     *
     * @return string
     */
    public function getLine(): string
    {
        return $this->line;
    }

    /**
     * Gets line number. This can be a string.
     *
     * @return string
     */
    public function getLineNumber(): string
    {
        return $this->lineNumber;
    }

    /**
     * Sets line content.
     *
     * @param string $lineNumber
     *
     * @return GitDiffLine
     */
    public function setLineNumber($lineNumber): GitDiffLine
    {
        $this->lineNumber = $lineNumber;

        return $this;
    }
}
