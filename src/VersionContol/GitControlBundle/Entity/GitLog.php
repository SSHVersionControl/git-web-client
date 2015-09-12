<?php
// src/Acme/UserBundle/Entity/User.php

namespace VersionContol\GitControlBundle\Entity;



/**
 * Useful options for git log --pretty=format Option 	Description of Output
 * 
 * %H Commit hash
 * %h Abbreviated commit hash
 * %T Tree hash
 * %t Abbreviated tree hash
 * %P Parent hashes
 * %p Abbreviated parent hashes
 * %an Author name
 * %ae Author e-mail
 * %ad Author date (format respects the --date=option)
 * %ar Author date, relative
 * %cn Committer name
 * %ce Committer email
 * %cd Committer date
 * %cr Committer date, relative
 * %s Subject
 * 
 * You may be wondering what the difference is between author and committer. The author is the person who originally wrote the work, 
 * whereas the committer is the person who last applied the work. So, if you send in a patch to a project and one of the core members 
 * applies the patch, both of you get credit – you as the author, and the core member as the committer. 
 */
class GitLog
{
    /**
     * Commit hash
     * @var string 
     */
    protected $hash;
    
    /**
     * Abbreviated commit hash
     * @var string 
     */
    protected $abbrHash;
    
    /**
     * Tree hash
     * @var string 
     */
    protected $treeHash;
    
    /**
     * Abbreviated tree hash
     * @var string 
     */
    protected $abbrTreeHash;
    
     /**
     * Parent hashes
     * @var string 
     */
    protected $parentHashes;
    
    /**
     * Abbreviated parent hashes
     * @var string 
     */
    protected $abbrParentHashes;
    
    /**
     * Author name
     * @var string 
     */
    protected $authorName;
    
    /**
     * Author e-mail
     * @var string 
     */
    protected $authorEmail;
    
    /**
     * Author date (format respects the --date=option)
     * @var string 
     */
    protected $authorDate;
    
    /**
     * Author date, relative
     * @var string 
     */
    protected $authorRelative;
    
    /**
     * Committer name
     * @var string 
     */
    protected $committerName;
    
    /**
     * Committer email
     * @var string 
     */
    protected $committerEmail;
    
    /**
     * Committer date
     * @var string 
     */
    protected $committerDate;
    
    /**
     * Committer date, relative
     * @var string 
     */
    protected $committerDateRelative;
    
    /**
     * Subject
     * @var string 
     */
    protected $subject;
    
    /**
     * File name. Used only on single log entires
     * @var string 
     */
    protected $fileName;

    public function __construct($line){
        $logData = explode('|',$line);
        if(count($logData) >= 15){
            $this->setHash(trim($logData[0]));
            $this->setAbbrHash(trim($logData[1]));
            $this->setTreeHash(trim($logData[2]));
            $this->setAbbrTreeHash(trim($logData[3]));
            $this->setParentHashes(trim($logData[4]));
            $this->setAbbrParentHashes(trim($logData[5]));
            $this->setAuthorName(trim($logData[6]));
            $this->setAuthorEmail(trim($logData[7]));
            $this->setAuthorDate(trim($logData[8]));
            $this->setAuthorRelative(trim($logData[9]));
            $this->setCommitterName(trim($logData[10]));
            $this->setCommitterEmail(trim($logData[11]));
            $this->setCommitterDate(trim($logData[12]));
            $this->setCommitterDateRelative(trim($logData[13]));
            $this->setSubject(trim($logData[14]));
        }
    }
    
    public function getHash() {
        return $this->hash;
    }

    public function getAbbrHash() {
        return $this->abbrHash;
    }

    public function getTreeHash() {
        return $this->treeHash;
    }

    public function getAbbrTreeHash() {
        return $this->abbrTreeHash;
    }

    public function getParentHashes() {
        return $this->parentHashes;
    }

    public function getAbbrParentHashes() {
        return $this->abbrParentHashes;
    }

    public function getAuthorName() {
        return $this->authorName;
    }

    public function getAuthorEmail() {
        return $this->authorEmail;
    }

    public function getAuthorDate() {
        return $this->authorDate;
    }

    public function getAuthorRelative() {
        return $this->authorRelative;
    }

    public function getCommitterName() {
        return $this->committerName;
    }

    public function getCommitterEmail() {
        return $this->committerEmail;
    }

    public function getCommitterDate() {
        return $this->committerDate;
    }

    public function getCommitterDateRelative() {
        return $this->committerDateRelative;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setHash($hash) {
        $this->hash = $hash;
        return $this;
    }

    public function setAbbrHash($abbrHash) {
        $this->abbrHash = $abbrHash;
        return $this;
    }

    public function setTreeHash($treeHash) {
        $this->treeHash = $treeHash;
        return $this;
    }

    public function setAbbrTreeHash($abbrTreeHash) {
        $this->abbrTreeHash = $abbrTreeHash;
        return $this;
    }

    public function setParentHashes($parentHashes) {
        $this->parentHashes = $parentHashes;
        return $this;
    }

    public function setAbbrParentHashes($abbrParentHashes) {
        $this->abbrParentHashes = $abbrParentHashes;
        return $this;
    }

    public function setAuthorName($authorName) {
        $this->authorName = $authorName;
        return $this;
    }

    public function setAuthorEmail($authorEmail) {
        $this->authorEmail = $authorEmail;
        return $this;
    }

    public function setAuthorDate($authorDate) {
        $this->authorDate = $authorDate;
        return $this;
    }

    public function setAuthorRelative($authorRelative) {
        $this->authorRelative = $authorRelative;
        return $this;
    }

    public function setCommitterName($committerName) {
        $this->committerName = $committerName;
        return $this;
    }

    public function setCommitterEmail($committerEmail) {
        $this->committerEmail = $committerEmail;
        return $this;
    }

    public function setCommitterDate($committerDate) {
        $this->committerDate = $committerDate;
        return $this;
    }

    public function setCommitterDateRelative($committerDateRelative) {
        $this->committerDateRelative = $committerDateRelative;
        return $this;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }
    
    public function getFileName() {
        return $this->fileName;
    }

    public function setFileName($fileName) {
        $this->fileName = $fileName;
        return $this;
    }



    
}

