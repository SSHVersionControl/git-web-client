<?php

namespace VersionContol\GitControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IssueComment
 *
 * @ORM\Table(name="issue_comment", indexes={@ORM\Index(name="fk_issue_comment_ver_user1_idx", columns={"ver_user_id"})})
 * @ORM\Entity
 */
class IssueComment
{
    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \VersionContol\GitControlBundle\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="VersionContol\GitControlBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ver_user_id", referencedColumnName="id")
     * })
     */
    private $verUser;



    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return IssueComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return IssueComment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return IssueComment
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set verUser
     *
     * @param \VersionContol\GitControlBundle\Entity\User\User $verUser
     *
     * @return IssueComment
     */
    public function setVerUser(\VersionContol\GitControlBundle\Entity\User\User $verUser = null)
    {
        $this->verUser = $verUser;

        return $this;
    }

    /**
     * Get verUser
     *
     * @return \VersionContol\GitControlBundle\Entity\User\User
     */
    public function getVerUser()
    {
        return $this->verUser;
    }
}
