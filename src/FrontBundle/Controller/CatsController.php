<?php

namespace FrontBundle\Controller;

use FrontBundle\Entity\Category;
use FrontBundle\Form\CatForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
/**
 * @PreAuthorize("hasRole('ROLE_ADMIN') or hasRole('ROLE_RH')  or hasRole('ROLE_RH_LIMITE')")
 */
class CatsController extends Controller
{
    public function indexAction()
    {

        $postDatatable = $this->get("sg_datatables.cat");
        $postDatatable->buildDatatable();
        return $this->render('FrontBundle:Cats:index.html.twig', array('datatable'=>$postDatatable));

    }
    public function catsResultsAction()
    {

        $datatable = $this->get("sg_datatables.cat");
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();


    }
    public function addAction(Request $request)
    {
        $cat = new Category();
        $db = $this->getDoctrine()->getManager();

        $form = $this->createForm(new CatForm(), $cat, array(
            'cascade_validation' => true
        ));

        if($request->getMethod() == 'POST') {
            $form->bind($request);
            if($form->isValid()) {

                $db->persist($cat);
                $db->flush();
                $this->get('session')->getFlashBag()->add('info', 'La catégorie a été créee  avec succès');
                return $this->redirect($this->generateUrl('front_manage_cats'));

            } else {
                //@TODO Message d'erreur à ajouter
            }

        }

        return $this->render('FrontBundle:Cats:add.html.twig',  array('form' => $form->createView()));
    }

    public function editAction($id)
    {
        $request = Request::createFromGlobals();

        $db = $this->getDoctrine()->getManager();
        $repCat = $db->getRepository('FrontBundle:Category');

        $cat = $repCat->find($id);

        $form = $this->createForm(new CatForm(), $cat, array(
            'cascade_validation' => true
        ));

        if($request->getMethod() == 'POST') {
            $form->bind($request);
            if($form->isValid()) {

                $db->persist($cat);
                $db->flush();
                $this->get('session')->getFlashBag()->add('info', 'La catégorie a été modifiée avec succès');
                return $this->redirect($this->generateUrl('front_manage_cats'));

            } else {
                //@TODO Message d'erreur à ajouter
            }

        }

        return $this->render('FrontBundle:Cats:edit.html.twig',  array('form' => $form->createView(), 'idc'=> $cat->getId()));
    }

    public function removeAction($id)
    {
        $cat = $this->getDoctrine()
            ->getRepository('FrontBundle:Category')
            ->find($id);

        if (!$cat) {
            throw $this->createNotFoundException(
                'Aucune catégorie trouvée pour cet id : ' . $id
            );
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($cat);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', 'La catégorie '.$id.' a été supprimée avec succès');
        return $this->redirect($this->generateUrl("front_manage_cats"));
    }
}
