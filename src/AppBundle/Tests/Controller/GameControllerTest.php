<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GameControllerTest extends WebTestCase
{
    public function testSuccesFlow()
    {
        $client = static::createClient();

        $client->request('POST', '/games');

        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $location = $client->getResponse()->headers->get('Location');
        $crawler = $client->request('GET', $location);
        var_dump($crawler);
    }
}
