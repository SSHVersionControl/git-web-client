<?php

namespace VersionControl\GitControlBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use VMelnik\DoctrineEncryptBundle\Configuration\Encrypted;
use Symfony\Component\Validator\Constraints as Assert;
use VersionControl\GitControlBundle\Validator\Constraints as VersionControlAssert;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="VersionControl\GitControlBundle\Repository\ProjectEnvironmentFilePermRepository")
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
     * @var boolean
     * @ORM\Column(name="enable_file_permissions", type="boolean", nullable=true)
     */
    private $enableFilePermissions;
    
    /**
     * @var ProjectEnvironment
     * 
     * @ORM\OneToOne(targetEntity="VersionControl\GitControlBundle\Entity\ProjectEnvironment", mappedBy="projectEnvironmentFilePerm" )
     */
    private $projectEnvironment;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_owner_read", type="boolean", nullable=true)
     */
    private $permissionOwnerRead;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_owner_write", type="boolean", nullable=true)
     */
    private $permissionOwnerWrite;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_owner_execute", type="boolean", nullable=true)
     */
    private $permissionOwnerExecute;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_sticky_uid", type="boolean", nullable=true)
     */
    private $permissionStickyUid;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_group_read", type="boolean", nullable=true)
     */
    private $permissionGroupRead;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_group_write", type="boolean", nullable=true)
     */
    private $permissionGroupWrite;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_group_execute", type="boolean", nullable=true)
     */
    private $permissionGroupExecute;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_sticky_gid", type="boolean", nullable=true)
     */
    private $permissionStickyGid;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_other_read", type="boolean", nullable=true)
     */
    private $permissionOtherRead;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_other_write", type="boolean", nullable=true)
     */
    private $permissionOtherWrite;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_other_execute", type="boolean", nullable=true)
     */
    private $permissionOtherExecute;
    
    /**
     * @var boolean
     * @ORM\Column(name="permission_sticky_bit", type="boolean", nullable=true)
     */
    private $permissionStickyBit;
    
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
    
    public function getPermissionSticky() {
        return $this->permissionSticky;
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

    

    public function setEnableFilePermissions($enableFilePermissions) {
        $this->enableFilePermissions = $enableFilePermissions;
        return $this;
    }

    public function setProjectEnvironment(ProjectEnvironment $projectEnvironment) {
        $this->projectEnvironment = $projectEnvironment;
        return $this;
    }
  
    public function getPermissionOwnerRead() {
        return $this->permissionOwnerRead;
    }

    public function getPermissionOwnerWrite() {
        return $this->permissionOwnerWrite;
    }

    public function getPermissionOwnerExecute() {
        return $this->permissionOwnerExecute;
    }

    public function setPermissionOwnerRead($permissionOwnerRead) {
        $this->permissionOwnerRead = $permissionOwnerRead;
        return $this;
    }

    public function setPermissionOwnerWrite($permissionOwnerWrite) {
        $this->permissionOwnerWrite = $permissionOwnerWrite;
        return $this;
    }

    public function setPermissionOwnerExecute($permissionOwnerExecute) {
        $this->permissionOwnerExecute = $permissionOwnerExecute;
        return $this;
    }
    
    public function getPermissionStickyUid() {
        return $this->permissionStickyUid;
    }

    public function setPermissionStickyUid($permissionStickyUid) {
        $this->permissionStickyUid = $permissionStickyUid;
        return $this;
    }
    
    public function getPermissionGroupRead() {
        return $this->permissionGroupRead;
    }

    public function getPermissionGroupWrite() {
        return $this->permissionGroupWrite;
    }

    public function getPermissionGroupExecute() {
        return $this->permissionGroupExecute;
    }

    public function getPermissionStickyGid() {
        return $this->permissionStickyGid;
    }

    public function getPermissionOtherRead() {
        return $this->permissionOtherRead;
    }

    public function getPermissionOtherWrite() {
        return $this->permissionOtherWrite;
    }

    public function getPermissionOtherExecute() {
        return $this->permissionOtherExecute;
    }

    public function getPermissionStickyBit() {
        return $this->permissionStickyBit;
    }

    public function setPermissionGroupRead($permissionGroupRead) {
        $this->permissionGroupRead = $permissionGroupRead;
        return $this;
    }

    public function setPermissionGroupWrite($permissionGroupWrite) {
        $this->permissionGroupWrite = $permissionGroupWrite;
        return $this;
    }

    public function setPermissionGroupExecute($permissionGroupExecute) {
        $this->permissionGroupExecute = $permissionGroupExecute;
        return $this;
    }

    public function setPermissionStickyGid($permissionStickyGid) {
        $this->permissionStickyGid = $permissionStickyGid;
        return $this;
    }

    public function setPermissionOtherRead($permissionOtherRead) {
        $this->permissionOtherRead = $permissionOtherRead;
        return $this;
    }

    public function setPermissionOtherWrite($permissionOtherWrite) {
        $this->permissionOtherWrite = $permissionOtherWrite;
        return $this;
    }

    public function setPermissionOtherExecute($permissionOtherExecute) {
        $this->permissionOtherExecute = $permissionOtherExecute;
        return $this;
    }

    public function setPermissionStickyBit($permissionStickyBit) {
        $this->permissionStickyBit = $permissionStickyBit;
        return $this;
    }
    
    public function setFileMode($fileMode){
        //Does nothing
        return $this;
    }


    public function getFileMode(){
        $types = array('Owner','Group','Other');
        $mode = array();
        
        $mode['sticky'] = 0;
        if($this->getPermissionStickyUid()){
            $mode['sticky'] += 4;
        }
        if($this->getPermissionStickyGid()){
            $mode['sticky'] += 2;
        }
        if($this->getPermissionStickyBit()){
            $mode['sticky'] += 1;
        }    
        
        foreach($types as $type){
            $mode[$type] = 0;
            if(call_user_func(array($this, "getPermission".$type."Read"))){
                $mode[$type] += 4;
            }
            if(call_user_func(array($this, "getPermission".$type."Write"))){
                $mode[$type] += 2;
            }
            if(call_user_func(array($this, "getPermission".$type."Execute"))){
                $mode[$type] += 1;
            }
        }
        
        return implode('',$mode);
    }




}
