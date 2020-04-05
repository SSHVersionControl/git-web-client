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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;
use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

/**
 * Use PhpSecLib to make SSH2 requests.
 *
 * @link https://github.com/phpseclib/phpseclib
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class SecLibSshProcess implements SshProcessInterface
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
     * @var SSH2|null
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
     * @param string|null $publicKeyFile
     * @param string|null $privateKeyFile
     * @param string $passphrase
     *
     * @return array
     * @throws RunGitCommandException
     */
    public function run(
        array $commands,
        string $host,
        string $username,
        int $port = 22,
        ?string $password = null,
        ?string $publicKeyFile = null,
        ?string $privateKeyFile = null,
        ?string $passphrase = null
    ): array {
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
     * @param null $privateKey
     * @param null $privateKeyPassword
     *
     * @throws InvalidArgumentException
     */
    protected function connect(
        $host,
        $username,
        $port = 22,
        $password = null,
        $pubkeyFile = null,
        $privateKey = null,
        $privateKeyPassword = null
    ) {
        $this->shell = new SSH2($host, $port);

        if (!$this->shell) {
            throw new InvalidArgumentException(sprintf('SSH connection failed on "%s:%s"', $host, $port));
        }

        if (isset($username) && trim($privateKey)) {
            $key = new RSA();
            if ($privateKeyPassword) {
                $key->setPassword($privateKeyPassword);
            }
            $key->loadKey($privateKey);
            if (!$this->shell->login($username, $key)) {
                throw new InvalidArgumentException(sprintf('SSH authentication failed for user "%s" using private key',
                    $username, $pubkeyFile));
            }
        } elseif ($username && $password) {
            if (!$this->shell->login($username, $password)) {
                throw new InvalidArgumentException(sprintf('SSH authentication failed for user "%s"', $username));
            }
        }
        $this->shell->getServerPublicHostKey();

        $this->stdout = array();
        $this->stdin = array();
    }

    public function disconnect()
    {
        if ($this->shell) {
            //$this->shell->disconnect();
        }
    }

    /**
     * @param array $command
     *
     * @throws RunGitCommandException
     */
    protected function execute($command)
    {
        $this->shell->enableQuietMode();

        $stdOutput = $this->shell->exec($command);
        $stdError = $this->shell->getStdError();
        $exitStatus = $this->shell->getExitStatus();

        $stdout = explode("\n", $stdOutput);
        $stderr = array_filter(explode("\n", $stdError));

        if ($exitStatus != 0) {
            //print_r($stderr);
            throw new RunGitCommandException(
                sprintf(
                    "Error in command shell:%s \n Error Response:%s%s",
                    $command,
                    implode("\n", $stderr),
                    $stdOutput
                )
            );
        }

        $this->stdout = array_merge($this->stdout, $stdout);

        if (is_array($stderr)) {
            $this->stderr = array_merge($this->stderr, $stderr);

            if ($exitStatus === 0) {
                $this->stdout = array_merge($this->stdout, $stderr);
            }
        }
    }

    /**
     * Get exit status
     * @return false|int
     */
    public function getExitStatus()
    {
        return $this->shell->getExitStatus();
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
