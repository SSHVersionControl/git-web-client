<?php

namespace VersionContol\GitControlBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use VMelnik\DoctrineEncryptBundle\Configuration\Encrypted;
use Symfony\Component\Validator\Constraints as Assert;
use VersionContol\GitControlBundle\Validator\Constraints as VersionContolAssert;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="VersionContol\GitControlBundle\Repository\ProjectEnvironmentFilePermRepository")
 * @ORM\Table(name="project_environment_file_perm")
 */
class ProjectEnvironmentFilePerm{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    private $id;
    
    /**
     * @var string
     * @ORM\Column(name="file_owner", type="string", length=80, nullable=true)
     */
    private $fileOwner;
    
    /**
     * @var string
     * @ORM\Column(name="file_group", type="string", length=80, nullable=true)
     */
    private $fileGroup;
    
    /**
     * @var integer
     * @ORM\Column(name="permission_owner", type="integer", nullable=true)
     */
    private $permissionOwner;
    
    /**
     * @var integer
     * @ORM\Column(name="permission_group", type="integer", nullable=true)
     */
    private $permissionGroup;
    
    /**
     * @var integer
     * @ORM\Column(name="permission_other", type="integer", nullable=true)
     */
    private $permissionOther;
    
    /**
     * @var boolean
     * @ORM\Column(name="enable_file_permissions", type="boolean", nullable=true)
     */
    private $enableFilePermissions;
    

    /**
     * @var ProjectEnvironment
     * 
     * @ORM\OneToOne(targetEntity="VersionContol\GitControlBundle\Entity\ProjectEnvironment", mappedBy="projectEnvironmentFilePerm", cascade={"persist"}, orphanRemoval=true )
     */
    private $projectEnvironment;
    
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
     * 
     * @return string
     */
    public function getFileOwner() {
        return $this->fileOwner;
    }

    public function getFileGroup() {
        return $this->fileGroup;
    }

    public function getPermissionOwner() {
        return $this->permissionOwner;
    }

    public function getPermissionGroup() {
        return $this->permissionGroup;
    }

    public function getPermissionOther() {
        return $this->permissionOther;
    }

    public function getEnableFilePermissions() {
        return $this->enableFilePermissions;
    }

    public function getProjectEnvironment() {
        return $this->projectEnvironment;
    }

    public function setFileOwner($fileOwner) {
        $this->fileOwner = $fileOwner;
        return $this;
    }

    public function setFileGroup($fileGroup) {
        $this->fileGroup = $fileGroup;
        return $this;
    }

    public function setPermissionOwner($permissionOwner) {
        $this->permissionOwner = $permissionOwner;
        return $this;
    }

    public function setPermissionGroup($permissionGroup) {
        $this->permissionGroup = $permissionGroup;
        return $this;
    }

    public function setPermissionOther($permissionOther) {
        $this->permissionOther = $permissionOther;
        return $this;
    }

    public function setEnableFilePermissions($enableFilePermissions) {
        $this->enableFilePermissions = $enableFilePermissions;
        return $this;
    }

    public function setProjectEnvironment(ProjectEnvironment $projectEnvironment) {
        $this->projectEnvironment = $projectEnvironment;
        return $this;
    }


    
}
