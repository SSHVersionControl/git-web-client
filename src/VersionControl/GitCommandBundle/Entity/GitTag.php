<?php
// src/VersionControl/GitCommandBundle/Entity/FileInfo.php

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
 * Remote File Info.
 * 
 *  "git for-each-ref --format '%(refname:short)|%(subject)|%(taggerDate)|%(taggerName)|%(taggerEmail)|%(*objectname)|%(*objectname:short)' refs/tags  --sort=taggerDate";
        
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitTag{
    
    /**
     * Tag name.
     *
     * @var string
     */
    protected $name;
    
    /**
     * Tags subject.
     *
     * @var string
     */
    protected $subject;

    /**
     * Tag date.
     *
     * @var \DateTime
     */
    protected $taggerDate;

   
    
    /**
     * Taggers name.
     *
     * @var string
     */
    protected $taggerName;

    /**
     * Taggers email.
     *
     * @var string
     */
    protected $taggerEmail;
    
    /**
     * Commit hash tag points to.
     *
     * @var string
     */
    protected $commitHash;

    /**
     * Abbreviated commit hash that tag points to.
     *
     * @var string
     */
    protected $commitAbbrHash;



    public function __construct($line)
    {
        $data = explode('|', $line);
        if (count($data) >= 7) {
            $this->setName($data[0]);
            $this->setSubject($data[1]);
            $this->setTaggerDate(new \DateTime($data[2]));
            $this->setTaggerName($data[3]);
            $this->setTaggerEmail($data[4]);
            $this->setCommitHash($data[5]);
            $this->setCommitAbbrHash($data[6]);
        }
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function getTaggerDate()
    {
        return $this->taggerDate;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getTaggerName()
    {
        return $this->taggerName;
    }

    public function getTaggerEmail()
    {
        return $this->taggerEmail;
    }

    public function getCommitHash()
    {
        return $this->commitHash;
    }

    public function getCommitAbbrHash()
    {
        return $this->commitAbbrHash;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setTaggerDate(\DateTime $taggerDate)
    {
        $this->taggerDate = $taggerDate;
        return $this;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function setTaggerName($taggerName)
    {
        $this->taggerName = $taggerName;
        return $this;
    }

    public function setTaggerEmail($taggerEmail)
    {
        $this->taggerEmail = $taggerEmail;
        return $this;
    }

    public function setCommitHash($commitHash)
    {
        $this->commitHash = $commitHash;
        return $this;
    }

    public function setCommitAbbrHash($commitAbbrHash)
    {
        $this->commitAbbrHash = $commitAbbrHash;
        return $this;
    }


}
