<?php

namespace BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use FrontBundle\Entity\User;

class SecurityController extends Controller {

    public function loginAdminAction() {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
//        var_dump($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED'));
//          die('if');
        if ($this->get('security.context')->isGranted('ROLE_RH')) {
            return $this->redirect($this->generateUrl('back_homepage'));
        }

        $request = $this->getRequest();
        $session = $request->getSession();

        // On vérifie s'il y a des erreurs d'une précédente soumission du formulaire
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
//            die('if');
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
//            die('if');
        } else {
//            die('else');
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        return $this->render('BackBundle:Auth:login.html.twig', array(
                    // Valeur du précédent nom d'utilisateur entré par l'internaute
                    'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                    'error' => $error,
        ));
    }

    public function getPrintDepotAction($name) {
        $zipfile = '/docs_sign/' . $this->container->getParameter('client_id') . '/toprint/' . $name;

        $response = new Response();

        // Set headers
        $response = new BinaryFileResponse($zipfile);
        $response->setStatusCode(200);
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($name) . '"');
        $response->headers->set('Content-length', filesize($zipfile));

        // Send headers before outputting anything

        return $response;
    }

}
