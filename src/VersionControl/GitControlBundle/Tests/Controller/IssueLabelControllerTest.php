<?php

namespace VersionControl\GitControlBundle\Tests\Controller;

class IssueLabelControllerTest extends BaseControllerTestCase
{
    public function testCompleteScenario()
    {
        $user = $this->createAuthorizedClient();
        // Create a new client to browse the application

        $project = $this->getProject($user);

        // List Issue labels
        $listLabelURL = $this->client->getContainer()->get('router')->generate('issuelabels', array('id' => $project->getId()));
        $crawler = $this->client->request('GET', $listLabelURL, array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET '.$listLabelURL);

        //Click New Issue Label Link
        $newIssueLabelLink = $crawler->filter('a:contains("New Label")')->link();
        $crawler = $this->client->request($newIssueLabelLink->getMethod(), $newIssueLabelLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET '.$newIssueLabelLink->getUri());

        // Fill in the new issue label form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'versioncontrol_gitcontrolbundle_issuelabel[title]' => 'Test Label',
            'versioncontrol_gitcontrolbundle_issuelabel[hexColor]' => 'f2f2f2',
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for creating new issue label');
        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Test Label")')->count(), 'Missing element a:contains("Test Label")');

         // Edit the issue label
        $issueLabelEditLink = $crawler->filter('a:contains("Test Label")')->link();
        $crawler = $this->client->request($issueLabelEditLink->getMethod(), $issueLabelEditLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));

        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Test Label")')->count(), 'Missing element h1:contains("Test Label") on Issue label show page');

        $form = $crawler->selectButton('Update')->form(array(
            'versioncontrol_gitcontrolbundle_issuelabel[title]' => 'Test Label Edit',
            'versioncontrol_gitcontrolbundle_issuelabel[hexColor]' => 'f2ece4',
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $this->assertGreaterThan(0, $crawler->filter('a:contains("Test Label Edit")')->count(), 'Missing element a:contains("Test Label Edit")');

        //Click Delete link
        $issueLabelDeleteLink = $crawler->filter('a:contains("Delete")')->link();
        $crawler = $this->client->request($issueLabelDeleteLink->getMethod(), $issueLabelDeleteLink->getUri(), array(), array(), array(
             'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $crawler = $this->client->followRedirect();
        $this->assertNotRegExp('/Test Label Edit/', $this->client->getResponse()->getContent());
    }
}
