<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitControlBundle\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Twig extension to create a link for arround text starting with issue{number} 
 * or iss{number}
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class CommitHashLinkExtension extends \Twig_Extension {
    
    private $generator;

    private $routeName;

    private $routeParameters;

    private $routeRelative;

    private $requestStack;
        
    
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }
        
    /**
     * {@inheritDoc}
     */
    public function getName() {
            return 'versioncontrol_commit_hash_link';
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('commitHashLink', 
                    array($this, 'commitHashLink')
                    ,array('is_safe' => array('html'))),
        );
    }

    /**
     * 
     * @param string $text
     * @param integer $projectId
     * @return string
     */
    public function commitHashLink($text, $projectId) {
       
        $matches = array();
        if (preg_match('/\b[0-9a-f]{7,40}\b/', $text, $matches)) {
            foreach($matches as $commitHash){
                if($this->validateCommitHash($commitHash)){
                    $url = $this->generateCommitHistoryUrl($commitHash,$projectId);
                    $link = '<a href="'.$url.'" title="Related to Commit Hash '.$commitHash.'">'.$commitHash.'</a>';
                    $text = str_replace($commitHash,$link,$text);
                }
            }
        }
         return $text;
    }
    
    /**
     * Validates the Commit hash to check if its a valid hash
     * @return boolean
     * @todo Add Git Hash Validation. Not sure yet how to validate.
     */
    protected function validateCommitHash($commitHash){
        return true;
    }
    
    /**
    * Gerenates a url
    * href="{{ path('project_commitdiff', { 'id': project.id, 'commitHash': log.abbrHash})}}"
    * @param integer $value Page paramater value
    * @return string Url
    */
    protected function generateCommitHistoryUrl($commitHash,$projectId,$routeName = 'project_commitdiff',$paramaterName='commitHash'){

        $parameters = array($paramaterName => $commitHash,'id' => $projectId);

        return $this->generator->generate($routeName, $parameters, UrlGeneratorInterface::RELATIVE_PATH);
    }
    
}