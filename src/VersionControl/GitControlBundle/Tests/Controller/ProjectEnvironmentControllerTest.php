<?php

namespace VersionControl\GitControlBundle\Tests\Controller;


class ProjectEnvironmentControllerTest extends BaseControllerTestCase
{

    public function testNewGitProjectEnvironmentScenario()
    {
        $user = $this->createAuthorizedClient();
        // Create a new client to browse the application
        
        $project = $this->getProject($user);

        
        // List users for project
        $url = $this->client->getContainer()->get('router')->generate('projectenvironment_new', array('id'=>$project->getId()));
        $crawler = $this->client->request('GET', $url,array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET ".$url);
        
        $gitPath = $this->createTempFolder('NewRepo');
        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'versioncontrol_gitcontrolbundle_projectenvironment[title]'  => 'Test New Project Environment',
            'versioncontrol_gitcontrolbundle_projectenvironment[description]'  => 'Test project environment with new git folder creation',
            'versioncontrol_gitcontrolbundle_projectenvironment[path]'  => $gitPath,
        ));
        
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for creating new project environment");
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Test New Project Environment")')->count(), 'Missing element a:contains(""Test New Project Environment")');

    }
    
    /**
     * Test exisitng Git project. Test does not create a git repo so we test that 
     * project path does not contain a git repo ;-(
     * @todo Does not validate in crawler. no idea why. needs more debugging
     */
    public function testExistingGitProjectEnvironmentScenario()
    {
        $user = $this->createAuthorizedClient();
        // Create a new client to browse the application
        
        $project = $this->getProject($user);

        
        // List users for project
        $url = $this->client->getContainer()->get('router')->generate('projectenvironment_existing', array('id'=>$project->getId()));
        $crawler = $this->client->request('GET', $url,array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /userprojects/");

        $gitPath = $this->createTempFolder('NewExistingRepo');
 
        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'versioncontrol_gitcontrolbundle_projectenvironment[title]'  => 'Test New Project Environment with Existing git Repo',
            'versioncontrol_gitcontrolbundle_projectenvironment[description]'  => 'Test project environment with existing git folder creation',
            'versioncontrol_gitcontrolbundle_projectenvironment[path]'  => $gitPath,
        ));
        
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for creating new existing git repo project environment");
        
        //$html = $crawler->html();
        //$showUrl = $this->client->getRequest()->getUri();
        //$this->assertGreaterThan(0, $crawler->filter('.alert-danger')->count(), 'Missing validation errors when there should be some. Element .alert-danger is missing.'.$showUrl);

    }
    
    public function testCloneGitProjectEnvironmentScenario()
    {
        $user = $this->createAuthorizedClient();
        // Create a new client to browse the application
        
        $project = $this->getProject($user);

        
        // List users for project
        $url = $this->client->getContainer()->get('router')->generate('projectenvironment_clone', array('id'=>$project->getId()));
        $crawler = $this->client->request('GET', $url,array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /userprojects/");
        
        $gitPath = $this->createTempFolder('NewCloneRepo');
        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'versioncontrol_gitcontrolbundle_projectenvironment[title]'  => 'Test Clone Project Environment',
            'versioncontrol_gitcontrolbundle_projectenvironment[description]'  => 'Test Clone project environment with new git folder creation',
            'versioncontrol_gitcontrolbundle_projectenvironment[path]'  => $gitPath,
            'versioncontrol_gitcontrolbundle_projectenvironment[gitCloneLocation]'  => 'https://github.com/SSHVersionControl/test.git',
        ));
        
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for creating new project environment");
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Test Clone Project Environment")')->count(), 'Missing element a:contains("Test Clone Project Environment")');


    }

}
