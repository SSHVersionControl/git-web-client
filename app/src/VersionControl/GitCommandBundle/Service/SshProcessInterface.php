<?php
/*
 * This file is part of the Version Control package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Service;

/**
 * Interface for SSH Process.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
interface SshProcessInterface
{
    /**
     * @param string $glue
     */
    public function getStdout($glue = "\n");

    /**
     * @param string $glue
     */
    public function getStderr($glue = "\n");

    /**
     * Runs the SSH command.
     *
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
    ): array;

    public function disconnect();

    /**
     * @return false|int
     */
    public function getExitStatus();
}
