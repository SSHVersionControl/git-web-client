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

/**
 * Uses php SSH2 library to run SSH Process.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class SshProcess implements SshProcessInterface
{
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
    protected $stdout = [];

    /**
     * @var array
     */
    protected $stderr = [];

    /**
     * @var array
     */
    private $stdin = [];

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
     * @return void
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
            $this->connect($host, $username, $port, $password, $publicKeyFile, $privateKeyFile, $passphrase);
        }

        foreach ($commands as $command) {
            $this->execute($command);
        }

        return $this->stdout;
    }

    /**
     * Resets out puts for next command.
     */
    protected function reset(): void
    {
        $this->stdout = [];
        $this->stdin = [];
        $this->stderr = [];
    }

    /**
     * @param string $host
     * @param string $username
     * @param int $port
     * @param null|string $password
     * @param null|string $publicKeyFile
     * @param null|string $privateKeyFile
     * @param null|string $passphrase
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function connect(
        string $host,
        string $username,
        int $port = 22,
        ?string $password = null,
        ?string $publicKeyFile = null,
        ?string $privateKeyFile = null,
        ?string $passphrase = null
    ): void {
        $this->session = ssh2_connect($host, $port);

        if (!$this->session) {
            throw new InvalidArgumentException(sprintf('SSH connection failed on "%s:%s"', $host, $port));
        }

        if (isset($username) && $publicKeyFile !== null && $privateKeyFile !== null) {
            if (!ssh2_auth_pubkey_file($this->session, $username, $publicKeyFile, $privateKeyFile, $passphrase)) {
                throw new InvalidArgumentException(
                    sprintf('SSH authentication failed for user "%s" with public key "%s"', $username, $publicKeyFile)
                );
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

        $this->stdout = [];
        $this->stdin = [];
    }

    public function disconnect(): void
    {
        if ($this->shell) {
            fclose($this->shell);
        }
    }

    /**
     * @param string $command
     *
     * @throws RuntimeException
     */
    protected function execute(string $command): void
    {
        $outStream = ssh2_exec($this->session, $command);
        $errStream = ssh2_fetch_stream($outStream, SSH2_STREAM_STDERR);

        stream_set_blocking($outStream, true);
        stream_set_blocking($errStream, true);

        $stdout = explode("\n", stream_get_contents($outStream));
        $stderr = explode("\n", stream_get_contents($errStream));

        if (count($stderr) > 1) {
            throw new RuntimeException(
                sprintf(
                    "Error in command shell:%s \n Error Response:%s",
                    $command,
                    implode("\n", $stderr)
                )
            );
        }

        $this->stdout = array_merge($this->stdout, $stdout);

        if (is_array($stderr)) {
            $this->stderr = array_merge($this->stderr, $stderr);
        }

        fclose($outStream);
        fclose($errStream);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function getExitStatus()
    {
        if (count($this->stderr) > 0) {
            return 1;
        }

        return 0;
    }
}
