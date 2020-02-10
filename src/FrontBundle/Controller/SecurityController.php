<?php

namespace FrontBundle\Controller;

use FrontBundle\Entity\ActivateSalary;
use FrontBundle\Entity\RecoveryPassword;
use FrontBundle\Form\ChangePasswordForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use FrontBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\Session;
use JMS\SecurityExtraBundle\Annotation\Secure;

class SecurityController extends Controller {

    public function loginAction() {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
//        var_dump($this->get('security.context')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY'));

//        var_dump($this->get('security.context')->isGranted('ROLE_RH_LIMITE'));
//        var_dump($this->get('security.context')->isGranted('ROLE_RH_LIMITE'));
//        die('loginAction');
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->get('security.context')->isGranted('ROLE_RH')) {


                return $this->redirect($this->generateUrl('front_homepage'));
            }
            if ($this->get('security.context')->isGranted('ROLE_RH_LIMITE')) {


                return $this->redirect($this->generateUrl('front_homepage'));
            }

//            } elseif ('ROLE_USER') {
//                return $this->redirect($this->generateUrl('front_my_profile'));
//            }
            if ($this->get('security.context')->isGranted('ROLE_USER')) {
                return $this->redirect($this->generateUrl('front_my_profile'));
            }
        }

//         die('IS_AUTHENTICATED_ANONYMOUSLY');
        $request = $this->getRequest();
        $session = $request->getSession();

        // On vérifie s'il y a des erreurs d'une précédente soumission du formulaire
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
//        die('avant return ');
        return $this->render('FrontBundle:Auth:login.html.twig', array(
                    // Valeur du précédent nom d'utilisateur entré par l'internaute
                    'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                    'error' => $error,
        ));
    }

    public function passwordRecoveryAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $baseUrl = $request->getHttpHost();
        $error = null;
        if ($request->getMethod() == 'POST') {
            $salary = $em->getRepository('FrontBundle:Salary')->findOneBy(array('emailPerso' => $_POST['mail']));
            if (is_object($salary)) {
                $userMail = $salary->getUser();
                if ($userMail) {

                    $unique = uniqid();
                    $lienChangement = 'https://' . $baseUrl . '' . $this->generateUrl('forgot_password_url', array('code' => $unique));
                    $this->get('email_servies')->sendPasswordRecovery($salary, $lienChangement, $_POST['mail']);
                    $rec = new RecoveryPassword();
                    $rec->setCode($unique);
                    $rec->setDone(false);
                    $rec->setUser($userMail);
                    $em->persist($rec);
                    $em->flush();


                    $this->get('session')->getFlashBag()->add(
                            'info', ' Consultez votre boîte de réception pour trouver l\'e-mail permettant de réinitialiser votre mot de passe.'
                    );
                    return $this->redirect($this->generateUrl('front_homepage'));
                } else {
                    $error = "Veuillez saisir une adresse e-mail valide.";
                }
            } else {
                $error = "L'adresse Email n'existe pas.";
            }
        }


        return $this->render('FrontBundle:Auth:passwordRecovery.html.twig', array('error' => $error));
    }

    public function passwordChangeAction($code, Request $request) {

        $em = $this->getDoctrine()->getManager();
        $recovercode = $em->getRepository('FrontBundle:RecoveryPassword')->findOneBy(array('code' => $code));

        if ($recovercode) {
            if ($recovercode->getDone() == true) {
                $this->get('session')->getFlashBag()->add(
                        'erreur', 'Lien de changement de mot de passe expiré !'
                );
                return $this->redirect($this->generateUrl('front_homepage'));
            } else {

                $currentUser = $recovercode->getUser()->getId();

                $user = $this->getDoctrine()
                        ->getRepository('FrontBundle:User')
                        ->find($currentUser);

                $form = $this->createForm(new ChangePasswordForm(), $user);

                if ($request->getMethod() == 'POST') {

                    $var = $request->request->get('user');

                    if (!($var["password"]['first'] !== "" && strlen($var["password"]['first']) <= 5)) {

                        $this->get('session')->getFlashBag()->add(
                                'erreur', "Veuillez saisir un mot de passe d'au moins 5 caractères."
                        );

                        return $this->render('FrontBundle:Auth:passwordChange.html.twig', array('form' => $form->createView(), 'code' => $code));
                    }

                    $form->handleRequest($request);

                    if ($form->isValid()) {

                        $user->setSalt(md5(uniqid()));
                        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                        $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                        $user->setPassword($password);


                        $rec = $em->getRepository('FrontBundle:RecoveryPassword')->AccessPasswordChange($currentUser);


                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);

                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                                'info', ' vous pouvez vous connecter en indiquant votre login et votre nouveau mot de passe.'
                        );

                        return $this->redirect($this->generateUrl('front_homepage'));
                    }
                }

                return $this->render('FrontBundle:Auth:passwordChange.html.twig', array('form' => $form->createView(), 'code' => $code));
            }
        } else {
            $this->get('session')->getFlashBag()->add(
                    'info', 'Lien de changement de mot de passe expiré !'
            );
            return $this->redirect($this->generateUrl('front_homepage'));
        }
    }

    public function activateSalaryAction($code) {



//        die('icici');
        $em = $this->getDoctrine()->getManager();
        $recovercode = $em->getRepository('FrontBundle:ActivateSalary')->findOneBy(array('code' => $code));

        $request = Request::createFromGlobals();

//        var_dump($recovercode);
//         die('herre');
        if ($recovercode) {
            if ($recovercode->getDone() == true) {
//               var_dump($recovercode);
//                 die('$recovercode->getDone()');
                $this->get('session')->getFlashBag()->add(
                        'erreur', 'Ce salarié est déjà activé via ce lien'
                );
                return $this->redirect($this->generateUrl('front_login'));
            } else {
                $currentUser = $recovercode->getUser()->getId();
                $user = $this->getDoctrine()
                        ->getRepository('FrontBundle:User')
                        ->find($currentUser);



                $salary = $em->getRepository('FrontBundle:Salary')->findOneBy(array('user' => $user));

//                $user = $salary->getUser();

                $form = $this->createForm(new ChangePasswordForm(), $user);

//                var_dump($request->getMethod());
//                die('activateSalaryAction');


                if ($request->getMethod() == 'POST') {
                    $error = false;
                    $var = $request->request->get('user_change_pwd');
                    $dateNaissance = $request->request->get('date_naissance');
//                    $numSecu = $request->request->get('num_secu');
//                    $numSecu = str_replace(" ", "", $numSecu);
                    $login_salary = $request->request->get('num_secu');
                    $login_salary = str_replace(" ", "", $login_salary);

                    //$salary_num_secu_from_base = substr($salary->getNumSecu(), 0, 13);
//                    var_dump($dateNaissance);
//                    var_dump($login_salary);
//                    var_dump($var);
//                    var_dump($var["password"]['first']);
//                    var_dump($var["password"]['second']);
//                    die('$var');



                    $salary_num_secu_from_base = $salary->getNumSecu();
                    $isdate = false;
                    $isSecu = false;
                    $first_password_salary = $var["password"]['first'];
                    $encoder = $this->get('security.encoder_factory')->getEncoder($user);
//                    $bool = $encoder->isPasswordValid($user->getPassword(), $first_password_salary, $user->getSalt());
//                    var_dump($user);
//                    die('icicici');
//                    var_dump($var["password"]['first'] );
//                    die('bool');
//                    if (!$bool) {
//                        $this->get('session')->getFlashBag()->add(
//                                'erreur', "Mot de passe incorrect. Veuillez contacter votre service Ressources Humaines"
//                        );
//                        $error = true;
//                    }
                    if ($var["password"]['first'] == "" || strlen($var["password"]['first']) <= 5) {


                        $this->get('session')->getFlashBag()->add(
                                'erreur', "Veuillez saisir un mot de passe d'au moins 6 caractères."
                        );
                        $error = true;
                    }

                    if ($var["password"]['first'] !== $var["password"]['second']) {


                        $this->get('session')->getFlashBag()->add(
                                'erreur', "Les deux mots de passe doivent être identiques"
                        );

                        $error = true;
                    }

                    if (!isset($dateNaissance) || empty($dateNaissance)) {
                        $this->get('session')->getFlashBag()->add(
                                'erreur', "Veuillez saisir votre date de naissance."
                        );
                        $error = true;
                    } elseif ($dateNaissance == $salary->getBirthDay()->format('Y-m-d')) {
                        $isdate = true;
                    }

                    if (!isset($login_salary) || empty($login_salary)) {

                        $this->get('session')->getFlashBag()->add(
                                'erreur', "Veuillez saisir votre numéro de sécurité sociale."
                        );
                        $error = true;
                    }

                    if ($login_salary != $salary_num_secu_from_base) {
                        $this->get('session')->getFlashBag()->add(
                                'erreur', "Numéro de sécurité sociale incorrect. Veuillez contacter votre service Ressources Humaines"
                        );
                        $error = true;
                    } elseif ($login_salary == $salary_num_secu_from_base) {
                        $isSecu = true; // 
                    }

//                    var_dump($isdate);
//                    var_dump($isSecu);
//                    die('activateSalaryAction + POST');
                    if (!$isdate || !$isSecu) {
                        $this->get('session')->getFlashBag()->add(
                                'erreur', "Les informations renseignées sont différentes de celles communiquées par votre entreprise. Veuillez contacter votre service Ressources Humaines"
                        );
                        $error = true;
                    }
                    if ($error) {
                        return $this->render('FrontBundle:Auth:activate_salary.html.twig', array('form' => $form->createView(), 'code' => $code));
                    }

                    $form->handleRequest($request);

//                    die('form handleRequest');
                    if ($form->isValid()) {


                        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                        $salt = md5(uniqid());
                        $plainpassword = $var["password"]['first'];
//                        $password = hash('sha512', $salt . $plainpassword);
                        $password = $encoder->encodePassword($plainpassword, $salt);
                        $user->setSalt($salt);
                        $user->setPassword($password);
                        // preparation of date
                        //$salary->setBirthDay(\DateTime::createFromFormat('d/m/Y', $dateNaissance));
                        //$salary->setNumSecu($numSecu);
//                        $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                        $user->setIsActive(true);
                        $recovercode->setDone(true);
                        $em->flush();
//                        die('flush');
                        //send informational email

                        $this->container->get('email_servies')->sendAfterActivation($salary);
//                        var_dump('1');
//                        die('sendAfterActivation');
                        $this->get('session')->getFlashBag()->add(
                                'info', 'Vous pouvez vous connecter en indiquant votre login et votre nouveau mot de passe.'
                        );

                        return $this->redirect($this->generateUrl('front_login'));
                    }
//                    else{
//                           die('$form->isNotValid');
//                    }
                }
                return $this->render('FrontBundle:Auth:activate_salary.html.twig', array('form' => $form->createView(), 'code' => $code));
            }
        } else {
            return $this->redirect($this->generateUrl('front_login'));
        }
    }

    /**
     * @Secure(roles="ROLE_RH,ROLE_RH_LIMITE, ROLE_USER")
     */
    public function getDocAction($token = null) {
        $session = new Session();
        $db = $this->getDoctrine()->getManager();
        $id = $session->get('id');
        $repDoc = $db->getRepository('FrontBundle:Document');
        $doc = $repDoc->find($id);
        $current_user = $this->get('security.context')->getToken()->getUser();
        if (($doc->getSalary()->getUser()->getId() == $current_user->getId() && $doc->getDoc() == $token) || ($this->get('security.context')->isGranted('ROLE_RH') && $doc->getDoc() == $token)) {
            $response = new Response(file_get_contents('/docs_sign/' . $this->container->getParameter('client_id') . '/' . $token), 200);
            $response->headers->set('Content-Type', 'application/pdf');
            return $response;
        } else {
            return 'error';
        }
    }

    /**
     * @Secure(roles="ROLE_RH,ROLE_RH_LIMITE, ROLE_ADMIN")
     */
    public function sendActivationAction(Request $request) {

//        die('sendActivationAction');
        $isAjax = $request->isXmlHttpRequest();

        if ($isAjax) {
            $choices = $request->request->get('data');
            $token = $request->request->get('token');

            if (!$this->isCsrfTokenValid('multiselect', $token)) {
                throw new AccessDeniedException('The CSRF token is invalid.');
            }

            $em = $this->getDoctrine()->getManager();
            $repSalary = $em->getRepository('FrontBundle:Salary');

            foreach ($choices as $choice) {
                $salary = $repSalary->find($choice['value']);
                $email = $salary->getEmailPerso();
                if (is_null($email)) {
                    $email = $salary->getEmailPro();
                }

                if (isset($email) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {

                    $unique = uniqid();
                    $baseUrl = $request->getHttpHost();
                    $lienActivation = 'https://' . $baseUrl . '' . $this->generateUrl('activate_salary', array('code' => $unique));
                    $this->get('email_servies')->sendActivation($salary, $lienActivation);

                    $rec = new ActivateSalary();
                    $rec->setCode($unique);
                    $rec->setDone(false);
                    $rec->setUser($salary->getUser());
                    $em->persist($rec);

                    $salary->setActivationSended(true);
                    $salary->getUser()->setIsActive(false);
                    $em->flush();
                }
            }
            $em->flush();


            return new Response('Success', 200);
        }

        return new Response('Bad Request', 400);
    }

    /**
     * @Secure(roles="ROLE_RH,ROLE_RH_LIMITE, ROLE_ADMIN")
     */
    public function sendRelanceAction(Request $request) {

        $isAjax = $request->isXmlHttpRequest();

        if ($isAjax) {
            $choices = $request->request->get('data');
            $token = $request->request->get('token');

            if (!$this->isCsrfTokenValid('multiselect', $token)) {
                throw new AccessDeniedException('The CSRF token is invalid.');
            }

            $em = $this->getDoctrine()->getManager();
            $repSalary = $em->getRepository('FrontBundle:Salary');
            $repSW = $em->getRepository('FrontBundle:Document');

            foreach ($choices as $choice) {
                $listWait = $repSW->find($choice['value']);
                $salary = $listWait->getSalary();
                $email = $salary->getEmailPerso();
                if (is_null($email) || empty($email)) {
                    $email = $salary->getEmailPro();
                }
                if (isset($email) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {

                    $this->get('email_servies')->sendRelance($salary);
                }
            }



            return new Response('Success', 200);
        }

        return new Response('Bad Request', 400);
    }

}
