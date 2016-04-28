<?php

namespace VersionControl\GitControlBundle\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Twig extension to create a link for arround text starting with issue{number} 
 * or iss{number}
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class IssueNumberLinkExtension extends \Twig_Extension {
    
    private $generator;
   
    
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }
        
    /**
     * {@inheritDoc}
     */
    public function getName() {
            return 'versioncontrol_issue_number_link';
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('issueNumberLink',
                    array($this, 'issueNumberLink'),
                    array('is_safe' => array('html'))),
        );
    }

    /**
     * 
     * @param string $text
     * @param integer $projectId
     * @param boolean $modal
     * @return string
     */
    public function issueNumberLink($text, $projectId,$modal=false) {
       
        $matches = array();
        if (preg_match('/\s(#)(\d+)/i', $text, $matches)) {
            foreach($matches as $issueId){
                if(is_numeric($issueId)){
                    if($modal){
                        $issueUrl = $this->generateIssueUrl($issueId,$projectId,'issue_show_modal');
                        $link = '<a href="'.$issueUrl.'"  data-remote="false" data-toggle="modal" data-target="#issueModal" title="Related to issue'.$issueId.'" class="ajax-modal non-ajax">#'.$issueId.'</a>';
                    }else{
                        $issueUrl = $this->generateIssueUrl($issueId,$projectId);
                        $link = '<a href="'.$issueUrl.'"  title="Related to issue #'.$issueId.'">#'.$issueId.'</a>';               
                    }
                    
                    $text = str_replace('#'.$issueId,$link,$text);
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
        protected function generateIssueUrl($issueId,$projectId,$routeName = 'issue_show',$issueParamaterName='issueId'){

            $parameters = array($issueParamaterName => $issueId,'id' => $projectId);
            //UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;
            return $this->generator->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
        }
    
}