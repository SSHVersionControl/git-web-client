<?php
namespace VersionControl\GithubIssueBundle\Repository;

use VersionControl\GitControlBundle\Entity\ProjectIssueIntegrator;

/**
 * Description of GithubBase
 *
 * @author paul
 */
abstract class GithubBase {
    
    /**
     * Github Client
     * @var \Github\Client()
     */
    protected $client;

    /**
     * ProjectIssueIntegrator Entity with data for repo, owner and authentication details
     * @var ProjectIssueIntegrator; 
     */
    protected $issueIntegrator;
    
    public function __construct() {
        $this->client = new \Github\Client(
               new \Github\HttpClient\CachedHttpClient(array('cache_dir' => $this->getCacheDir())) 
        );
    }
    
    public function getIssueIntegrator() {
        return $this->issueIntegrator;
    }

    public function setIssueIntegrator(ProjectIssueIntegrator $issueIntegrator) {
        $this->issueIntegrator = $issueIntegrator;
        return $this;
    }

    protected function authenticate(){
        $this->client->authenticate($this->issueIntegrator->getApiToken(), '', \Github\Client::AUTH_URL_TOKEN);
    }
    
    protected function getCacheDir(){
        $dir = dirname(__DIR__).'/../../../app/cache/githubcache';
 
        if(!file_exists($dir)){
             mkdir($dir, 0755);
        }
        return $dir;
    }
}
