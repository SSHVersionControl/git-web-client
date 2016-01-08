<?php
/*
 * This file is part of the Version Control package.
 * 
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionContol\GitControlBundle\Utility;

/**
 * Interface for SSH Process
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
interface SshProcessInterface
{
    /**
     * 
     * @param string $glue
     */
    public function getStdout($glue = "\n");

    /**
     * @param string $glue
     */
    public function getStderr($glue = "\n");

    /**
     * Runs the SSH command 
     * 
     * @param array $commands
     * @param string $host
     * @param string $username
     * @param integer $port
     * @param string $password
     * @param string $pubkeyFile
     * @param string $privkeyFile
     * @param string $passphrase
     * @return type
     */
    public function run(array $commands,$host,$username,$port=22,$password=null,$pubkeyFile=null,$privkeyFile=null,$passphrase=NULL);
    
    public function disconnect();
}

