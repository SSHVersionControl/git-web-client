<?php

namespace VersionControl\GitControlBundle\Tests\Controller;



class ProjectControllerTest extends BaseControllerTestCase
{
  
    public function testCompleteScenario()
    {
        $user = $this->createAuthorizedClient();
        // Create a new client to browse the application
        
        // List Issue labels
        $projectCreateURL = $this->client->getContainer()->get('router')->generate('project_new');
        

        // Create a new entry in the database
        $crawler = $this->client->request('GET', $projectCreateURL);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET ".$projectCreateURL);
        
        
        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'versioncontrol_gitcontrolbundle_project[title]'  => 'Test New Project',
            'versioncontrol_gitcontrolbundle_project[description]'  => 'Test the creation of a new project',
        ));

        $this->client->submit($form);
        
        //Redirects to Edit project page
        $crawler = $this->client->followRedirect();
        
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for edit project redirect");

        // Check title
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Test New Project")')->count(), 'Missing element a:contains("Test New Project")');

        $form = $crawler->selectButton('Update')->form(array(
            'versioncontrol_gitcontrolbundle_project[title]'  => 'Test Edit Project',
            'versioncontrol_gitcontrolbundle_project[description]'  => 'Test Edit project description',
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for Edit project form submit ");


        // Check the element contains an attribute with value equals "Foo"
        $this->assertInputValue($crawler,'versioncontrol_gitcontrolbundle_project[title]','Test Edit Project');
        $this->assertInputValue($crawler,'versioncontrol_gitcontrolbundle_project[description]','Test Edit project description');
        
        
        // Delete the entity
        /*$this->client->submit($crawler->selectButton('Delete')->form());
        $crawler = $this->client->followRedirect();

        // Check the entity has been delete on the list
        $this->assertNotRegExp('/Foo/', $this->client->getResponse()->getContent());*/
    }

}
