<?php

namespace VersionContol\GitControlBundle\Utility;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class SshProcess
{


    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var resource
     */
    protected $session;

    /**
     * @var resource
     */
    protected $shell;

    /**
     * @var array
     */
    protected $stdout;

    /**
     * @var array
     */
    protected $stderr;

    /**
     * //EventDispatcherInterface $eventDispatcher,
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param array $config
     */
    public function __construct()
    {
        //$this->dispatcher = $eventDispatcher;

        $this->session    = null;
        $this->shell      = null;
        $this->stdout     = array();
        $this->stdin      = array();
        $this->stderr     = array();
    }

    /**
     * @param string $glue
     * @return array|string
     */
    public function getStdout($glue = "\n")
    {
        if (!$glue) {
            $output = $this->stdout;
        }else{
           $output = implode($glue, $this->stdout); 
        }

        return $output;
    }

    /**
     * @param string $glue
     * @return array|string
     */
    public function getStderr($glue = "\n")
    {
        if (!$glue) {
            return $this->stderr;
        }

        return implode($glue, $this->stderr);
    }

    /**
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
    public function run(array $commands,$host,$username,$port=22,$password=null,$pubkeyFile=null,$privkeyFile=null,$passphrase=NULL)
    {
        $this->reset();
        
        if($this->shell === NULL){
            $this->connect($host,$username,$port,$password,$pubkeyFile,$privkeyFile,$passphrase);
        }
        
        foreach ($commands as $command) {
            $this->execute($command);
        }

       //$this->disconnect();

        return $this->stdout;
    }
    
    /**
     * Resets out puts for next command
     */
    protected function reset(){
        $this->stdout     = array();
        $this->stdin      = array();
        $this->stderr     = array();
    }

    /**
     * @throws \InvalidArgumentException|\RuntimeException
     * @param array $connection
     * @return void
     */
    protected function connect($host,$username,$port=22,$password=null,$pubkeyFile=null,$privkeyFile=null,$passphrase=NULL)
    {

        $this->session = ssh2_connect($host, $port);

        if (!$this->session) {
            throw new \InvalidArgumentException(sprintf('SSH connection failed on "%s:%s"', $host, $port));
        }

        if (isset($username) && $pubkeyFile != null && $privkeyFile != null) {
            if (!ssh2_auth_pubkey_file($username, $pubkeyFile, $privkeyFile, $passphrase)) {
                throw new \InvalidArgumentException(sprintf('SSH authentication failed for user "%s" with public key "%s"', $username, $pubkeyFile));
            }
        } else if ($username && $password) {
            if (!ssh2_auth_password($this->session, $username,$password)) {
                throw new \InvalidArgumentException(sprintf('SSH authentication failed for user "%s"', $username));
            }
        }

        $this->shell = ssh2_shell($this->session);

        if (!$this->shell) {
            throw new \RuntimeException(sprintf('Failed opening shell'));
        }

        $this->stdout = array();
        $this->stdin = array();
    }

    /**
     * @return void
     */
    protected function disconnect()
    {
        if($this->shell){
            fclose($this->shell);
        }
    }

    /**
     * @param array $command
     * @return void
     */
    protected function execute($command)
    {

        //$this->dispatcher->dispatch(Events::onDeploymentSshStart, new CommandEvent($command));

        $outStream = ssh2_exec($this->session, $command);
        $errStream = ssh2_fetch_stream($outStream, SSH2_STREAM_STDERR);

        stream_set_blocking($outStream, true);
        stream_set_blocking($errStream, true);

        $stdout = explode("\n", stream_get_contents($outStream));
        $stderr = explode("\n", stream_get_contents($errStream));

        if (count($stdout)) {

            //$this->dispatcher->dispatch(Events::onDeploymentRsyncFeedback, new FeedbackEvent('out', implode("\n", $stdout)));
        }

        if (count($stderr) > 1) {
            //print_r($stderr);
             throw new \RuntimeException(sprintf('Error in command shell:%s',$command));
            //$this->dispatcher->dispatch(Events::onDeploymentRsyncFeedback, new FeedbackEvent('err', implode("\n", $stderr)));
        }

        $this->stdout = array_merge($this->stdout, $stdout);

        if (is_array($stderr)) {
            $this->stderr = array_merge($this->stderr, $stderr);
        } else {
            //$this->dispatcher->dispatch(Events::onDeploymentSshSuccess, new CommandEvent($command));
        }

        fclose($outStream);
        fclose($errStream);
    }
    
    public function __destruct() {
        $this->disconnect();
    }

}


