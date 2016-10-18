<?php

namespace VersionControl\GitControlBundle\Tests\Controller;

use VersionControl\GitControlBundle\Tests\Controller\BaseControllerTestCase;

class DefaultControllerTest extends BaseControllerTestCase
{
    
    public function testList()
    {
        $this->createAuthorizedClient();

        $crawler = $this->client->request('GET', '/');

        $this->assertTrue($crawler->filter('html:contains("Your Git Projects")')->count() > 0);

    }
    
}
