<?php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\GitCommands;

/**
 * Interface to the Git Environment. Implement to object to
 * store location of git path and ssh details.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
interface GitEnvironmentInterface
{
    /**
     * Unique identifier.
     */
    public function getId();

    /**
     * Get Path to git folder.
     */
    public function getPath();

    /**
     * Get SSH value, true or false to use SSH.
     *
     * @return bool
     */
    public function getSsh();

    /**
     * Get SSH host.
     *
     * @return string
     */
    public function getHost();

    /**
     * Get SSH host.
     *
     * @return string
     */
    public function getPort();

    /**
     * Get SSH username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Get SSH password.
     *
     * @return string
     */
    public function getPassword();

    /**
     * Get Private Key Content.
     *
     * @return string
     */
    public function getPrivateKey();

    /**
     * Get Private Key Password.
     *
     * @return string
     */
    public function getPrivateKeyPassword();
}
