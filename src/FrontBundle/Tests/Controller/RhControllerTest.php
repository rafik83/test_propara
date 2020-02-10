<?php

namespace FrontBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RhControllerTest extends WebTestCase
{
    private $client = null;


    public function setUp()
    {
        
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'jenkinsrh',
            'PHP_AUTH_PW'   => 'jenkinsrh',
        ));
    }

    public function testAccueil()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($crawler->filter('html:contains("Gestion des salariés")')->count() > 0);
    }


}
