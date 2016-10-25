<?php

namespace VersionControl\GitControlBundle\Tests\Controller;

class IssueControllerTest extends BaseControllerTestCase
{
    public function testCompleteScenario()
    {
        $user = $this->createAuthorizedClient();
        // Create a new client to browse the application

        $project = $this->getProject($user);

        // Create a new entry in the database
        $url = $this->client->getContainer()->get('router')->generate('issues', array('id' => $project->getId()));

        $crawler = $this->client->request('GET', $url, array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        //$this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET ".$url);
        //$crawler = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET '.$url);

        $newIssueLink = $crawler->filter('.box-tools > form > .pull-right > a')->link();
        $crawler = $this->client->request($newIssueLink->getMethod(), $newIssueLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET '.$url);

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'versioncontrol_gitcontrolbundle_issue[title]' => 'Test',
            'versioncontrol_gitcontrolbundle_issue[description]' => 'Test of new issue',
            'versioncontrol_gitcontrolbundle_issue[issueMilestone]' => '',
            // ... other fields to fill
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for creating new issue');
        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Test")')->count(), 'Missing element a:contains("Test")');

        //Show Issue
        $issueShowLink = $crawler->filter('a:contains("Test")')->link();
        $crawler = $this->client->request($issueShowLink->getMethod(), $issueShowLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Test")')->count(), 'Missing element h1:contains("Test") on Issue show page');

        // Edit the entity
        $issueEditLink = $crawler->filter('a:contains("Edit")')->link();
        $crawler = $this->client->request($issueEditLink->getMethod(), $issueEditLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        $form = $crawler->selectButton('Update')->form(array(
            'versioncontrol_gitcontrolbundle_issue[title]' => 'Test Update',
            'versioncontrol_gitcontrolbundle_issue[description]' => 'Test editing of issue',
            'versioncontrol_gitcontrolbundle_issue[issueMilestone]' => '',
            // ... other fields to fill
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $this->assertGreaterThan(0, $crawler->filter('.alert:contains("successfully updated")')->count(), 'Missing element .alert:contains("successfully updated")');

        // Check the element contains an attribute with value equals "Foo"
        $this->assertInputValue($crawler, 'versioncontrol_gitcontrolbundle_issue[title]', 'Test Update');
        $this->assertTextAreaValue($crawler, 'versioncontrol_gitcontrolbundle_issue[description]', 'Test editing of issue');

        //Back to Show Issue
        $crawler = $this->client->request($issueShowLink->getMethod(), $issueShowLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        //Close Issue
        $issueCloseLink = $crawler->filter('a:contains("Close")')->link();
        $crawler = $this->client->request($issueCloseLink->getMethod(), $issueCloseLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(0, $crawler->filter('.alert:contains("has been closed")')->count(), 'Missing notification element .alert:contains("has been closed")');

        //Back to Show Issue
        $crawler = $this->client->request($issueShowLink->getMethod(), $issueShowLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        //Re-Open Issue
        $issueReOpenLink = $crawler->filter('a:contains("Re-Open")')->link();
        $crawler = $this->client->request($issueReOpenLink->getMethod(), $issueReOpenLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(0, $crawler->filter('.alert:contains("has been re-opened")')->count(), 'Missing notification element .alert:contains("has been re-opened")');

        /*
        // Delete the entity
        $this->client->submit($crawler->selectButton('Delete')->form());
        $crawler = $this->client->followRedirect();

        // Check the entity has been delete on the list
        $this->assertNotRegExp('/Foo/', $this->client->getResponse()->getContent());*/
    }
}
