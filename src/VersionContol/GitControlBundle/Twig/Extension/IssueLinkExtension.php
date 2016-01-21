<?php

namespace VersionContol\GitControlBundle\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Twig extension to create a link for arround text starting with issue{number} 
 * or iss{number}
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class IssueLinkExtension extends \Twig_Extension {
    
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
            return 'versioncontrol_issuelink';
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('issueLink', array($this, 'issueLink')),
        );
    }

    /**
     * 
     * @param string $text
     * @return string
     */
    public function issueLink($text) {
       
        $matches = array();
        if (preg_match('/(issue|iss|issu)(\d+)/i', $text, $matches)) {
            foreach($matches as $issueId){
                if(is_numeric($issueId)){
                    $issueUrl = $this->generateIssueUrl($issueId);
                    return '<a href="'.$issueUrl.'"  data-remote="false" data-toggle="modal" data-target="#issueModal" title="Related to issue'.$issueId.'" class="ajax-modal">'.$text.'</a>';
                }
            }
        }
         return $text;
    }
    
    /**
         * Gerenates a url
         * 
         * @param integer $value Page paramater value
         * @return string Url
         */
        protected function generateIssueUrl($issueId,$routeName = 'issue_show_modal',$issueParamaterName='id'){

            $parameters = array($issueParamaterName => $issueId);
            return $this->generator->generate($routeName, $parameters, false);
        }
    
}