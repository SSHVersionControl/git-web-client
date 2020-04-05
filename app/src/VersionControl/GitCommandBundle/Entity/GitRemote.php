<?php
// src/VersionControl/GitCommandBundle/Entity/GitRemote.php

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
 * Git Remote Respository Entity.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitRemote
{
    protected $shortName;

    protected $longName;

    protected $state;

    public function getShortName()
    {
        return $this->shortName;
    }

    public function getLongName()
    {
        return $this->longName;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function setLongName($longName)
    {
        $this->longName = $longName;

        return $this;
    }

    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }
}
