<?php

namespace BackBundle\Controller;

use BackBundle\Form\RhUserEditForm;
use BackBundle\Form\RhUserForm;
use FrontBundle\Entity\RhUser;
use FrontBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BackBundle\Form\ZipForm;
use BackBundle\Entity\ZipFile;

class ManageUsersController extends Controller {

    public function indexAction() {

        $postDatatable = $this->get("sg_datatables.rh");
        $postDatatable->buildDatatable();
        return $this->render('BackBundle:Manage:index.html.twig', array('datatable' => $postDatatable));
    }

    public function rhResultsAction() {
        $datatable = $this->get("sg_datatables.rh");
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }

    public function getAllRole() {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('FrontBundle:Role')->findAll();
        $Roles = array();


        foreach ($entity as $key => $value) {
            if ($value->getName() != 'SALARY') {
                $Roles[$key]['id'] = $value->getId();
                $Roles[$key]['role'] = $value->getRole();
            }
        }
        return $Roles;
    }

    public function addAction(Request $request) {
        $RhUser = new RhUser();
        $db = $this->getDoctrine()->getManager();
        $usersRoles = $this->getAllRole();
        $repoRole = $db->getRepository('FrontBundle:Role');

        $form = $this->createForm(new RhUserForm(), $RhUser, array(
            'cascade_validation' => true
        ));

//        var_dump($form->createView());
//        die('addAction manage user');

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
//            $form->bind($request);
            if ($form->isValid()) {
                
                $idRole = $form->get('extra_user_role')->getData();
                $entity = $repoRole->find($idRole);
                $RhUser->getUser()->addRole($entity);
                $RhUser->getUser()->setIsActive(true);
                $RhUser->getUser()->setSalt(uniqid(mt_rand())); // Unique salt for user
                $RhUser->getUser()->setIsActive(true);
                $plain_password = $RhUser->getUser()->getPassword();
                // Set encrypted password
                $encoder = $this->container->get('security.encoder_factory')
                        ->getEncoder($RhUser->getUser());
                $password = $encoder->encodePassword($RhUser->getUser()->getPassword(), $RhUser->getUser()->getSalt());
                $RhUser->getUser()->setPassword($password);
                $db->persist($RhUser);
                $db->flush();
                $this->get('session')->getFlashBag()->add('info', 'Votre utilisateur est crée avec succès');
                return $this->redirect($this->generateUrl('manage_users'));
            } else {
                $errors = array();
            }
        }

        return $this->render('BackBundle:Manage:add.html.twig', array(
                    'form' => $form->createView(),
                    'roles' => $usersRoles
        ));
    }

    public function editAction($id) {
        $request = Request::createFromGlobals();

        $db = $this->getDoctrine()->getManager();
        $repUserRh = $db->getRepository('FrontBundle:RhUser');

        $user = $repUserRh->find($id);

        $form = $this->createForm(new RhUserEditForm(), $user, array(
            'cascade_validation' => true
        ));

        $old_password = $user->getUser()->getPassword();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $plain_password = $user->getUser()->getPassword();

                if (isset($plain_password) && !empty($plain_password)) {
                    $user->getUser()->setIsActive(true);
                    $user->getUser()->setSalt(uniqid(mt_rand())); // Unique salt for user
                    $user->getUser()->setIsActive(true);

                    // Set encrypted password
                    $encoder = $this->container->get('security.encoder_factory')
                            ->getEncoder($user->getUser());
                    $password = $encoder->encodePassword($user->getUser()->getPassword(), $user->getUser()->getSalt());
                    $user->getUser()->setPassword($password);
                } else {
                    $user->getUser()->setPassword($old_password);
                }
                $db->persist($user);
                $db->flush();
                $this->get('session')->getFlashBag()->add('info', 'Votre utilisateur a été modifié avec succès');
                return $this->redirect($this->generateUrl('manage_users'));
            } else {
                $errors = array();
            }
        }

        return $this->render('BackBundle:Manage:edit.html.twig', array('form' => $form->createView(), 'iduser' => $user->getId()));
    }

    public function removeAction($id) {
        $user = $this->getDoctrine()
                ->getRepository('FrontBundle:RhUser')
                ->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                    'Aucun utilisateur trouvé pour cet id : ' . $id
            );
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'L\'utilisateur ' . $id . ' a été supprimé avec succès');
        return $this->redirect($this->generateUrl("manage_users"));
    }

}
