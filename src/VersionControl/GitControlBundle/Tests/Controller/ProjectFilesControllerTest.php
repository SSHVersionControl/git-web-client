<?php

namespace VersionControl\GitControlBundle\Tests\Controller;

class ProjectFilesControllerTest extends BaseControllerTestCase
{
    public function testCompleteScenario()
    {
        $user = $this->createAuthorizedClient();
        // Create a new client to browse the application
        
        $project = $this->getProject($user);
        
        // Get File List
        $projectFileListURL = $this->client->getContainer()->get('router')->generate('project_filelist',array('id'=>$project->getId()));

        // Create a new entry in the database
        $crawler = $this->client->request('GET', $projectFileListURL);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET '.$projectFileListURL);

        
    }
}
