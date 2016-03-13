<?php
// src/VersionControl/GithubIssueBundle/Entity/User.php
namespace VersionControl\GithubIssueBundle\Entity;

use VersionContol\GitControlBundle\Entity\Issues\IssueUserInterface;

/**
 */
class User implements IssueUserInterface
{
    
    /**
     *
     * @var integer
     */
    protected $id;
    
    /**
     *
     * @var string
     */
    protected $name;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }


}

