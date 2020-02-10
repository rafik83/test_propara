<?php
namespace BackBundle\Service;

use \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class Sha512 implements  PasswordEncoderInterface
{
    public function encodePassword($raw, $salt) {

        return hash('sha512',$salt.$raw);
    }


    public function isPasswordValid($encoded, $raw, $salt) {
        return $encoded === $this->encodePassword($raw,$salt);
    }

}