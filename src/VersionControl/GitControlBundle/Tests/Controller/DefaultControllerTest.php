<?php

namespace VersionControl\GitControlBundle\Tests\Controller;

class DefaultControllerTest extends BaseControllerTestCase
{
    public function testList()
    {
        $this->createAuthorizedClient();

        $crawler = $this->client->request('GET', '/');

        $this->assertTrue($crawler->filter('html:contains("Your Git Projects")')->count() > 0);
    }
}
