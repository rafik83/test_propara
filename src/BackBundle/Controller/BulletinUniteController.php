<?php

namespace BackBundle\Controller;

use BackBundle\Entity\Bu;
use BackBundle\Form\BuForm;
use BackBundle\Form\RhUserEditForm;
use BackBundle\Form\RhUserForm;
use FrontBundle\Entity\RhUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BackBundle\Form\ZipForm;
use BackBundle\Entity\ZipFile;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
/**
 * @PreAuthorize("hasRole('ROLE_ADMIN') or hasRole('ROLE_RH')  or hasRole('ROLE_RH_LIMITE')")
 */
class BulletinUniteController extends Controller
{

    public function listAction()
    {

        $postDatatable = $this->get("sg_datatables.bu");
        $postDatatable->buildDatatable();
        return $this->render('BackBundle:Bu:index.html.twig', array('datatable'=>$postDatatable));

    }
    public function resultAction()
    {
        $datatable = $this->get("sg_datatables.bu");
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }
    public function addAction(Request $request)
    {
        
        
        $db = $this->getDoctrine()->getManager();

        $class = new Bu();

        $form = $this->createForm(new BuForm(), $class);
//        var_dump($request->getMethod());
        

        if($request->getMethod() == 'POST')
        {
            $form->handleRequest($request);

            if($form->isValid()) {
                if(!is_null($class->getBulletin()))
                {
                    $file = $class->getBulletin();
                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                    //move the file to the directory where documents are stored
                    $dir = '/docs_sign/' . $this->container->getParameter('client_id') . '/unite/';
                    $file->move($dir, $fileName);
                    $class->setBulletin($fileName);
                }

                $this->get('session')->getFlashBag()->add('info', 'Le nouveau bulletin a été ajouté avec succès');
                $class->setCommentError('En attente');
                $db->persist($class);
                $db->flush();
                return $this->redirect($this->generateUrl('bu_list'));
            }

        }
        
//        die('fin');
        return $this->render('BackBundle:Bu:add.html.twig',  array('form' => $form->createView()));
    }
}