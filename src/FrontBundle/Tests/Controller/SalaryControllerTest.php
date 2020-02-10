<?php

namespace FrontBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SalaryControllerTest extends WebTestCase
{
    private $client = null;


    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'jenkinsSalary',
            'PHP_AUTH_PW'   => 'jenkinsSalary',
        ));
    }


    public function testAccueil()
    {
        $crawler = $this->client->request('GET', '/myprofile');
        $this->assertTrue($crawler->filter('html:contains("Mon espace")')->count() > 0);
    }
}
