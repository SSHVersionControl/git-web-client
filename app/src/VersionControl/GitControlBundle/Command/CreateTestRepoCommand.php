<?php

/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;

/**
 * Command to create a new admin user for the version control application.
 *
 * @author Paul Schweppe<paulschweppe@gmail.com>
 */
class CreateTestRepoCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('version:repo:create')
            ->setDescription('Create a test repo to use on heroku.')
            ->setDefinition(array(
                new InputArgument('directory', InputArgument::REQUIRED, 'The directory to create the repo'),
                
            ))
            ->setHelp(<<<'EOT'
The <info>version:repo:create</info> command creates a new git repository with files:

  <info>php app/console version:repo:create</info>

This interactive shell will ask you for a directory.


EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('directory');

        $this->createRepo($path);
        
        $output->writeln(sprintf('Created repo in <comment>%s</comment>', $path));
    }


    /**
     * Creates a new git repository.
     *
     * @param string $path
     *
     */
    protected function createRepo($path)
    {

        $this->addFolder($path,'test');
        
        $path = rtrim($path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'test';
        
        $this->runLocalCommand($path, 'git init');
        $this->runLocalCommand($path, 'git config user.email "paulschweppe@gmail.com"');
        $this->runLocalCommand($path, 'git config user.name "Paul Schweppe"');
            
        $this->addFile($path,'testfile.txt',null,'This is a file with content');
        $this->runLocalCommand($path,'git add -A');
        
        $message = 'inital commit';
        $author = 'Paul Schweppe <paulschweppe@gmail.com>';
        $commitCmd = 'git commit -m '.escapeshellarg($message).' --author='.escapeshellarg($author);
        
        $this->runLocalCommand($path,$commitCmd);
             
    }
    
    /**
     * @param string      $name    file name
     * @param string|null $folder  folder name
     * @param null        $content content
     */
    protected function addFile($path,$name, $folder = null, $content = null)
    {

        $filename = $folder == null ?
                $path.DIRECTORY_SEPARATOR.$name :
                $path.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$name;
        $handle = fopen($filename, 'w');
        $fileContent = $content == null ? 'test content' : $content;
        if(false !== fwrite($handle, $fileContent)){ 
            sprintf('unable to write the file %s', $name);   
        }
        fclose($handle);
    }
    
      /**
     * @param string $name name
     */
    protected function addFolder($path,$name)
    {
        $fs = new Filesystem();
        $fs->mkdir($path.DIRECTORY_SEPARATOR.$name);
    }
    
    /**
     * Run local command.
     *
     * @param string $command
     *
     * @return string Commands response
     */
    private function runLocalCommand($path,$command)
    {
        $fullCommand = sprintf('cd %s && %s', $path, $command);

        //Run local commands
        if (is_array($command)) {
            //$finalCommands = array_merge(array('cd',$this->gitPath,'&&'),$command);
            $builder = new ProcessBuilder($command);
            $builder->setPrefix('cd '.$path.' && ');
            $process = $builder->getProcess();
        } else {
            $process = new Process($fullCommand);
        }

        //Run Proccess
        $process->run();

        $this->exitCode = $process->getExitCode();

        $response = '';
        // executes after the command finishes
        if ($process->isSuccessful()) {
            $response = $process->getOutput();
            if (trim($process->getErrorOutput()) !== '') {
                $response = $process->getErrorOutput();
            }
        } else {
            if (trim($process->getErrorOutput()) !== '') {
                throw new RunGitCommandException($process->getErrorOutput());
            }
        }

        return $response;
    }
}
