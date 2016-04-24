<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace VersionControl\GitControlBundle\Installer;

use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;

class DatabaseInstall
{
    /** @var Connection */
    protected $db;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    protected $output;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }


    protected function runQueriesFromFile($file)
    {
        $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents($file)));

        if (!$this->output->isQuiet()) {
            $this->output->writeln(
                sprintf(
                    'Executing %d queries from %s on database %s',
                    count($queries),
                    $file,
                    $this->db->getDatabase()
                )
            );
        }

        foreach ($queries as $query) {
            $this->db->exec($query);
        }
    }
    
    public function importSchema()
    {
        $this->runQueriesFromFile(
            'src/VersionControl/GitControlBundle/Installer/dbscript/schema.sql'
        );
    }

    public function importData()
    {
        $this->runQueriesFromFile(
            'src/VersionControl/GitControlBundle/Installer/dbscript/data.sql'
        );
    }
}

