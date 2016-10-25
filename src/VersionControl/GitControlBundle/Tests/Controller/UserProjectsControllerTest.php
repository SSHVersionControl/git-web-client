<?php

namespace VersionControl\GitControlBundle\Tests\Controller;

class UserProjectsControllerTest extends BaseControllerTestCase
{
    public function testCompleteScenario()
    {
        $user = $this->createAuthorizedClient();
        // Create a new client to browse the application

        $project = $this->getProject($user);

        // List users for project
        $url = $this->client->getContainer()->get('router')->generate('members_list', array('id' => $project->getId()));
        $crawler = $this->client->request('GET', $url, array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET /userprojects/');
    }
}
