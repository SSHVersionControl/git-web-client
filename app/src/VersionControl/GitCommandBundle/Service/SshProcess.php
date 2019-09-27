<?php
/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Service;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Uses php SSH2 library to run SSH Process.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class SshProcess implements SshProcessInterface
{
    /**
     * @var EventDispatcherInterface
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
     * //EventDispatcherInterface $eventDispatcher,.
     *
     */
    public function __construct()
    {
        //$this->dispatcher = $eventDispatcher;

        $this->session = null;
        $this->shell = null;
        $this->stdout = array();
        $this->stdin = array();
        $this->stderr = array();
    }

    /**
     * @param string $glue
     *
     * @return array|string
     */
    public function getStdout($glue = "\n")
    {
        if (!$glue) {
            $output = $this->stdout;
        } else {
            $output = implode($glue, $this->stdout);
        }

        return $output;
    }

    /**
     * @param string $glue
     *
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
     * @param array $commands
     * @param string $host
     * @param string $username
     * @param int $port
     * @param string $password
     * @param string $pubkeyFile
     * @param string $privkeyFile
     * @param string $passphrase
     *
     * @return array
     * @throws RuntimeException
     */
    public function run(
        array $commands,
        $host,
        $username,
        $port = 22,
        $password = null,
        $pubkeyFile = null,
        $privkeyFile = null,
        $passphrase = null
    ) {
        $this->reset();

        if ($this->shell === null) {
            $this->connect($host, $username, $port, $password, $pubkeyFile, $privkeyFile, $passphrase);
        }

        foreach ($commands as $command) {
            $this->execute($command);
        }

        //$this->disconnect();

        return $this->stdout;
    }

    /**
     * Resets out puts for next command.
     */
    protected function reset()
    {
        $this->stdout = array();
        $this->stdin = array();
        $this->stderr = array();
    }

    /**
     * @param $host
     * @param $username
     * @param int $port
     * @param null $password
     * @param null $pubkeyFile
     * @param null $privkeyFile
     * @param null $passphrase
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function connect(
        $host,
        $username,
        $port = 22,
        $password = null,
        $pubkeyFile = null,
        $privkeyFile = null,
        $passphrase = null
    ) {
        $this->session = ssh2_connect($host, $port);

        if (!$this->session) {
            throw new InvalidArgumentException(sprintf('SSH connection failed on "%s:%s"', $host, $port));
        }

        if (isset($username) && $pubkeyFile != null && $privkeyFile != null) {
            if (!ssh2_auth_pubkey_file($username, $pubkeyFile, $privkeyFile, $passphrase)) {
                throw new InvalidArgumentException(sprintf('SSH authentication failed for user "%s" with public key "%s"',
                    $username, $pubkeyFile));
            }
        } elseif ($username && $password) {
            if (!ssh2_auth_password($this->session, $username, $password)) {
                throw new InvalidArgumentException(sprintf('SSH authentication failed for user "%s"', $username));
            }
        }

        $this->shell = ssh2_shell($this->session);

        if (!$this->shell) {
            throw new RuntimeException(sprintf('Failed opening shell'));
        }

        $this->stdout = array();
        $this->stdin = array();
    }

    public function disconnect()
    {
        if ($this->shell) {
            fclose($this->shell);
        }
    }

    /**
     * @param array $command
     *
     * @throws RuntimeException
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
            throw new RuntimeException(sprintf("Error in command shell:%s \n Error Response:%s", $command,
                implode("\n", $stderr)));
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

    public function __destruct()
    {
        $this->disconnect();
    }
}
