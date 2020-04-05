<?php

namespace VersionControl\GitControlBundle\Tests\Controller;

class IssueMilestoneControllerTest extends BaseControllerTestCase
{
    public function testCompleteScenario()
    {
        $user = $this->createAuthorizedClient();
        // Create a new client to browse the application

        $project = $this->getProject($user);

        // List Issue milestones
        $listMilestoneURL = $this->client->getContainer()->get('router')->generate('issuemilestones', array('id' => $project->getId()));
        $crawler = $this->client->request('GET', $listMilestoneURL, array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET '.$listMilestoneURL);

        //Click New Issue Milestone Link
        $newIssueMilestoneLink = $crawler->filter('a:contains("New Milestone")')->link();
        $crawler = $this->client->request($newIssueMilestoneLink->getMethod(), $newIssueMilestoneLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET '.$newIssueMilestoneLink->getUri());

        // Fill in the new issue milestone form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'versioncontrol_gitcontrolbundle_issuemilestone[title]' => 'Test Milestone',
            'versioncontrol_gitcontrolbundle_issuemilestone[description]' => 'Test Milestone Description',
            'versioncontrol_gitcontrolbundle_issuemilestone[dueOn][date]' => '2016-10-06',
            'versioncontrol_gitcontrolbundle_issuemilestone[dueOn][time]' => '00:00',
        ));
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for creating new issue milestone');

        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Test Milestone")')->count(), 'Missing element h1:contains("Test Milestone")');
        $showUrl = $this->client->getRequest()->getUri();

         // Edit the issue milestone
        $issueMilestoneEditLink = $crawler->filter('a:contains("Edit")')->link();
        $crawler = $this->client->request($issueMilestoneEditLink->getMethod(), $issueMilestoneEditLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for edit issue milestone');

        //Submit Edit form
        $form = $crawler->selectButton('Update')->form(array(
            'versioncontrol_gitcontrolbundle_issuemilestone[title]' => 'Test Milestone Edit',
            'versioncontrol_gitcontrolbundle_issuemilestone[description]' => 'Edit Test Milestone Description',
            'versioncontrol_gitcontrolbundle_issuemilestone[dueOn][date]' => '2016-10-06',
            'versioncontrol_gitcontrolbundle_issuemilestone[dueOn][time]' => '00:00',
        ));
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Test Milestone Edit")')->count(), 'Missing element h1:contains("Test Milestone Edit")');

        //Close Milestone
        $issueMilestoneCloseLink = $crawler->filter('a:contains("Close")')->link();
        $crawler = $this->client->request($issueMilestoneCloseLink->getMethod(), $issueMilestoneCloseLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $crawler = $this->client->followRedirect();
        $this->assertNotRegExp('/Test Milestone Edit/', $this->client->getResponse()->getContent());

        //Open Show Issue milestone again
        $crawler = $this->client->request('GET', $showUrl, array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for edit issue milestone');

        //Re-open Milestone
        $issueMilestoneReOpenLink = $crawler->filter('a:contains("Re-open")')->link();
        $crawler = $this->client->request($issueMilestoneReOpenLink->getMethod(), $issueMilestoneReOpenLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(0, $crawler->filter('.alert:contains("has been opened")')->count(), 'Missing element .alert:contains("successfully updated")');

        //Close Milestone Again
        $issueMilestoneCloseLink = $crawler->filter('a:contains("Close")')->link();
        $crawler = $this->client->request($issueMilestoneCloseLink->getMethod(), $issueMilestoneCloseLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $crawler = $this->client->followRedirect();
        $this->assertNotRegExp('/Test Milestone Edit/', $this->client->getResponse()->getContent());
    }
}
