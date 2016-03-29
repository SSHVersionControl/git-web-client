<?php
namespace VersionControl\GitlabIssueBundle\Repository;

use VersionControl\GitControlBundle\Entity\ProjectIssueIntegrator;

/**
 * Description of GitlabBase
 *
 * @author paul
 */
abstract class GitlabBase {
    
    /**
     * Gitlab Client
     * @var \Gitlab\Client()
     */
    protected $client;

    /**
     * ProjectIssueIntegrator Entity with data for repo, owner and authentication details
     * @var ProjectIssueIntegrator; 
     */
    protected $issueIntegrator;
    
    public function __construct() {
        $this->client = new \Gitlab\Client('https://git.fluid-rock.com/api/v3/'); // change here
        
    }
    
    public function getIssueIntegrator() {
        return $this->issueIntegrator;
    }

    public function setIssueIntegrator(ProjectIssueIntegrator $issueIntegrator) {
        $this->issueIntegrator = $issueIntegrator;
        return $this;
    }

    protected function authenticate(){
        $this->client->authenticate($this->issueIntegrator->getApiToken(), \Gitlab\Client::AUTH_URL_TOKEN);
    }
    
    protected function getCacheDir(){
        $dir = dirname(__DIR__).'/../../../app/cache/githubcache';
 
        if(!file_exists($dir)){
             mkdir($dir, 0755);
        }
        return $dir;
    }
}
