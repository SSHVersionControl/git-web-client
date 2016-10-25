<?php
// src/Acme/UserBundle/Entity/User.php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Entity\User;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use VersionControl\GitControlBundle\Entity\Issues\IssueUserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="ver_user")
 */
class User extends BaseUser implements IssueUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Please enter your name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max=255,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="VersionControl\GitControlBundle\Entity\UserProjects", mappedBy="user", cascade={"persist"} )
     */
    private $userProjects;

    /**
     * Is admin Base on roles eg ROLE_ADMIN.
     *
     * @var bool
     */
    private $admin;

    /**
     * @ORM\Column(name="github_id", type="string", length=255, nullable=true)
     */
    protected $githubId;

    /** @ORM\Column(name="github_access_token", type="string", length=255, nullable=true) */
    protected $githubAccessToken;

    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->userProjects = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Add user access to project.
     *
     * @param \VersionControl\GitControlBundle\Entity\UserProjects $userProject
     *
     * @return resource
     */
    public function addUserProjects(\VersionControl\GitControlBundle\Entity\UserProjects $userProject)
    {
        $userProject->setUser($this);
        $this->userProjects[] = $userProject;

        return $this;
    }

    /**
     * Remove user access to project.
     *
     * @param \VersionControl\GitControlBundle\Entity\UserProjects $userProject
     */
    public function removeUserProjects(\VersionControl\GitControlBundle\Entity\UserProjects $userProject)
    {
        $this->userProjects->removeElement($userProject);
    }

    /**
     * Get all user access to project.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserProjects()
    {
        return $this->userProjects;
    }

    /**
     * Set all user access to projects.
     *
     * @param \Doctrine\Common\Collections\Collection $userProjects
     */
    public function setUserProjects(\Doctrine\Common\Collections\Collection $userProjects)
    {
        $this->userProjects = $userProjects;
    }

    public function getAdmin()
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;
        $this->setRoles(array('ROLE_ADMIN'));

        return $this;
    }

    public function getGithubId()
    {
        return $this->githubId;
    }

    public function getGithubAccessToken()
    {
        return $this->githubAccessToken;
    }

    public function setGithubId($githubId)
    {
        $this->githubId = $githubId;

        return $this;
    }

    public function setGithubAccessToken($githubAccessToken)
    {
        $this->githubAccessToken = $githubAccessToken;

        return $this;
    }
}
