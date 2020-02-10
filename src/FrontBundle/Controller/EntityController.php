<?php

namespace FrontBundle\Controller;

use FrontBundle\Entity\Category;
use FrontBundle\Entity\Company;
use FrontBundle\Form\CatForm;
use FrontBundle\Form\CompanyForm;
use FrontBundle\Form\CompanyType;
use FrontBundle\Form\CompanyEditType;
use FrontBundle\Entity\RhUser;
use FrontBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Doctrine\ORM\QueryBuilder;

/**
 * @PreAuthorize("hasRole('ROLE_ADMIN') or hasRole('ROLE_RH')  or hasRole('ROLE_RH_LIMITE')")
 */
class EntityController extends Controller {

    public function CompanyAction() {


        $current_user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $companies = array();
        $RH_company = array();
        if ($this->get('security.context')->isGranted('ROLE_RH_LIMITE')) {


            $RhUser = $this->getRhUser($current_user->getId());
            $idRhUser = $RhUser[0]['id'];
            $arrayCompany = $this->getIdCompanyBy($idRhUser);
            
            
            
            if (count($arrayCompany) == 1) {
                $companyId = $arrayCompany[0]['company_id'];
                $CompanyRhUser = $em->getRepository('FrontBundle:Company')->find($companyId);
                $RH_company = $CompanyRhUser;
                
            }
            if (count($arrayCompany) > 1) {
               
                foreach ($arrayCompany as $key => $value) {
                 
                    $companyId = $value['company_id'];
                    $CompanyRhUser = $em->getRepository('FrontBundle:Company')->find($companyId);
                    $RH_company[$key]['id'] = $CompanyRhUser->getId();
                    $RH_company[$key]['nom'] = $CompanyRhUser->getNom();
                    
                    
                }
                
               
                
                
            }
            $postDatatable = $this->get("sg_datatables.company");
            $postDatatable->buildDatatable();
//            $array = array(0=>'1',1=>'2');
            return $this->render('FrontBundle:Entity:company_rh.html.twig', array(
                        'datatable' => $postDatatable,
                        'rh_company' => $RH_company,
                        'count_length' => count($RH_company)
            ));
        }
        if ($this->get('security.context')->isGranted('ROLE_RH')) {
//             die('ROLE_RH');
            $postDatatable = $this->get("sg_datatables.company");
            $postDatatable->buildDatatable();
            return $this->render('FrontBundle:Entity:company.html.twig', array(
                        'datatable' => $postDatatable
            ));
        }



        $postDatatable = $this->get("sg_datatables.company");
        $postDatatable->buildDatatable();
        return $this->render('FrontBundle:Entity:company.html.twig', array(
                    'datatable' => $postDatatable
        ));
    }

    public function getRhUser($user_id) {

        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".rh_users  ";
        $query .= ' ';
        $query .= "where  user_id ='" . $user_id . "'";
        $query .= ' ';
        $queryfinale = $query; //. $query_where . $query_and;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getIdCompanyBy($RhUserId) {

        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".company_rh_user  ";
        $query .= ' ';
        $query .= "where  rh_user_id  ='" . $RhUserId . "'";
        $query .= ' ';
        $queryfinale = $query; //. $query_where . $query_and;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function oldCompanyAction() {

        $em = $this->getDoctrine()->getManager();
        $idAllCompanies = array();
        $companies = array();
        $responsable_company = array();
//        die('1');
        if ($this->get('security.context')->isGranted('ROLE_RH_LIMITE')) {
            $findAllCompany = $em->getRepository('FrontBundle:Company')->findAll();
            foreach ($findAllCompany as $key => $value) {
                $idAllCompanies[$key]['id'] = $value->getId();
            }

            $responsable = $em->getRepository('FrontBundle:Responsable')->findAll();
            foreach ($responsable as $key => $value) {
                $companies[$key] = $value->getCompanies();
            }
            $index = 0;
            foreach ($companies as $key => $array_collection) {
                foreach ($array_collection as $key => $value) {
                    $responsable_company[$index]['id'] = $value->getId();
                    $responsable_company[$index]['nom'] = $value->getNom();
                    $index ++;
                }
            }
            $postDatatable = $this->get("sg_datatables.company");
            $postDatatable->buildDatatable();
            return $this->render('FrontBundle:Entity:company_rh.html.twig', array(
                        'datatable' => $postDatatable,
                        'all_company_id' => $idAllCompanies,
                        'responsable_company' => $responsable_company
            ));
        }
        if ($this->get('security.context')->isGranted('ROLE_RH')) {
            $postDatatable = $this->get("sg_datatables.company");
            $postDatatable->buildDatatable();
            return $this->render('FrontBundle:Entity:company.html.twig', array(
                        'datatable' => $postDatatable
            ));
        }

//        var_dump($responsable_company);
//        die('$responsable_company');
    }

    public function companyResultAction() {
        $datatable = $this->get("sg_datatables.company");
        $datatable->buildDatatable();
        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        return $query->getResponse();

//        $id = 1;
//        $options = array('rowId' => $id);
//        $datatable = $this->get('sg_datatables.company');
//        $datatable->buildDatatable($options);
////        $datatable->buildDatatable();
//        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
//        $function = function (QueryBuilder $qb) use ($id) {
//            $qb->andWhere('id = :id');
//            $qb->setParameter('id', $id);
//        };
//
//        $query->addWhereAll($function);
//        return $query->getResponse();
//        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
//        return $query->getResponse();
//        $query->buildQuery();
//        $qb = $query->getQuery();
//        $qb->andWhere('id = :id');
//        $qb->setParameter('id', $id);
//        $query->setQuery($qb);
//        return $query->getResponse(false);
    }

    public function groupAction() {

        $postDatatable = $this->get("sg_datatables.group");
        $postDatatable->buildDatatable();
        return $this->render('FrontBundle:Entity:group.html.twig', array('datatable' => $postDatatable));
    }

    public function groupResultAction() {

        $datatable = $this->get("sg_datatables.group");
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }

    public function getAllRhUser() {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('FrontBundle:RhUser')->findAll();
        $rhUsers = array();


        foreach ($entity as $key => $value) {
            if ($value->getId() != 1) {
                $rhUsers[$key]['id'] = $value->getId();
                $rhUsers[$key]['nom'] = $value->getNom();
            }
        }
        return $rhUsers;
    }

    public function addCompanyAction(Request $request) {
        $company = new Company();
        $db = $this->getDoctrine()->getManager();
        $rhUsers = $this->getAllRhUser();
        $repoRhUser = $db->getRepository('FrontBundle:RhUser');
        $repoCompany = $db->getRepository('FrontBundle:Company');

//        $form = $this->createForm(new CompanyForm(), $company, array(
//            'cascade_validation' => true
//        ));
        $form = $this->createForm(new CompanyType(), $company, array(
            'cascade_validation' => true
        ));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $extraRhUsers = $form->get('extra_rh_user')->getData();
                $explode = explode(",", $extraRhUsers);
                foreach ($explode as $key => $value) {
                    $rhuser = $repoRhUser->find($value);
                    if ($rhuser) {
                        $company->addRhuser($rhuser);
                    }
                }
                $createdAt = new \DateTime("now");
                $company->setCreatedAt($createdAt);
                $db->persist($company);
                $db->flush();
                $this->get('session')->getFlashBag()->add('info', 'Entreprise est créée avec succès');
                $response = new Response('okk');
                return $response;
//                $this->get('session')->getFlashBag()->add('info', 'Entreprise est créée avec succès');
//                return $this->redirect($this->generateUrl('front_manage_company'));
            } else {
                //@TODO Message d'erreur à ajouter
            }
        }

        return $this->render('FrontBundle:Entity:add_company2.html.twig', array('form' => $form->createView(),
//                    'company' => $company,
                    'rhusers' => $rhUsers,
        ));
    }

    public function editCompanyAction($id) {
        $request = Request::createFromGlobals();
        $db = $this->getDoctrine()->getManager();
        $repCompany = $db->getRepository('FrontBundle:Company');
        $repoRhUser = $db->getRepository('FrontBundle:RhUser');
        $company = $repCompany->find($id);
        $rhUsers = $this->getAllRhUser();
        $oldIds = $company->getRhusers();

        $em = $this->getDoctrine()->getManager();

//        $form = $this->createForm(new CompanyForm(), $company, array(
//            'cascade_validation' => true
//        ));
        $formEdit = $this->createForm(new CompanyEditType(), $company, array(
            'cascade_validation' => true
        ));

        if ($request->getMethod() == 'POST') {

            $formEdit->handleRequest($request);
            if ($formEdit->isValid()) {


                foreach ($oldIds as $key => $value) {
                    $removeRhuser = $repoRhUser->find($value);
                    $company->removeRhuser($removeRhuser);
                }



                $extraRhUsers = $formEdit->get('extra_rh_user')->getData();
                $explode = explode(",", $extraRhUsers);
                foreach ($explode as $key => $value) {
                    $rhuser = $repoRhUser->find($value);
                    if ($rhuser) {
                        $company->addRhuser($rhuser);
                    }
                }


                $em->persist($company);
                $db->flush();

                $this->get('session')->getFlashBag()->add('info', 'Entreprise modifiée avec succès');
                $response = new Response('ok');
                return $response;
                //return $this->redirect($this->generateUrl('front_manage_company'));
            } else {
                //@TODO Message d'erreur à ajouter
            }
        }

        return $this->render('FrontBundle:Entity:edit_company2.html.twig', array(
                    'formEdit' => $formEdit->createView(),
                    'company' => $company,
                    'rhusers' => $rhUsers,
                    'idsRhUsers' => $company->getRhusers()
//                    'id_company' => $company->getId()
        ));
    }

    public function removeCompanyAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $company = $em
                ->getRepository('FrontBundle:Company')
                ->find($id);

        if (!$company) {
            throw $this->createNotFoundException(
                    'Aucune entreprise trouvée pour cet id : ' . $id
            );
        }



        try {
            $em->beginTransaction();

            // Remove salary
            $em->remove($company);

            $em->flush();
            $em->commit();


            $this->get('session')->getFlashBag()->add('info', 'L \'entreprise ' . $company->getNom() . ' a été supprimée avec succès');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->get('session')->getFlashBag()->add('erreur', 'Impossible de supprimer cette entreprise');
            $em->rollback();
            $em->close();
        }

        return $this->redirect($this->generateUrl("front_manage_company"));
    }

}
