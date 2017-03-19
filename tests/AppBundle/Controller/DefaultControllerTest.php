<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();


        $crawler = $client->request('GET', '/');

        $resp = $client->getResponse();
        $this->assertEquals(200, $resp->getStatusCode(), "200 expected, got " . $resp->getContent());
    }
}
