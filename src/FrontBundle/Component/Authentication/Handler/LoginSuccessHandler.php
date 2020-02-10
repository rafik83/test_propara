<?php

namespace FrontBundle\Component\Authentication\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{

    protected $router;
    protected $security;

    public function __construct(Router $router, SecurityContext $security)
    {
        $this->router = $router;
        $this->security = $security;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
       
//        var_dump($this->security->isGranted('ROLE_USER'));
         
        if ($this->security->isGranted('ROLE_RH')) {
            $response = new RedirectResponse($this->router->generate('front_homepage'));
        } elseif ($this->security->isGranted('ROLE_USER')) {
            $response = new RedirectResponse($this->router->generate('front_my_profile'));
        } else {
// redirect the user to where they were before the login process begun.
           
            $referer_url = $request->headers->get('referer');
            $response = new RedirectResponse($referer_url);
        }

//        var_dump($response);
//        die('fin');
        return $response;
    }

}