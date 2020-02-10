<?php

namespace FrontBundle\Controller;

use FrontBundle\Entity\ActivateSalary;
use FrontBundle\Entity\Category;
use FrontBundle\Entity\CoDoc;
use FrontBundle\Entity\Document;
use FrontBundle\Entity\Salary;
use FrontBundle\Entity\Company;
use FrontBundle\Entity\User;
use FrontBundle\Form\CoDocForm;
use FrontBundle\Form\MyPreferencesForm;
use FrontBundle\Form\SalaryEditForm;
use FrontBundle\Form\SalaryForm;
use FrontBundle\Form\DocumentForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Session\Session;

class SalaryController extends Controller {

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function indexAction() {
        //$this->get("size.counter")->getCount();
        $postDatatable = $this->get("sg_datatables.salary");
        $postDatatable->buildDatatable();
        return $this->render('FrontBundle:Salary:index.html.twig', array('datatable' => $postDatatable));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function salaryResultsAction() {
        $datatable = $this->get("sg_datatables.salary");
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function addAction(Request $request) {
        $salary = new Salary();
        $db = $this->getDoctrine()->getManager();

//        die('icici + submit + post');
        $form = $this->createForm(new SalaryForm(), $salary, array(
            'cascade_validation' => true
        ));
        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {
//                $plain_password = $form->get('extra_user_pwd')->getData();
//                var_dump($plain_password);

                $dataxx = $params = $this->getRequest()->request->all();
//                var_dump($dataxx['salary_form']);// $dataxx['salary_form']
//                die('icici + submit + post');
                $User = NULL;
                $user_id = null;
                $username = $dataxx['salary_form']['extra_user_name']; //$form->get('extra_user_name')->getData();
                $plain_password = $dataxx['salary_form']['extra_user_pwd']; //$form->get('extra_user_pwd')->getData();
                $matricule_salary = $form->getData()->getMatricule();
                $company_id = $form->getData()->getCompany()->getId();
                $num_secu_salary = $form->getData()->getNumSecu();
                $existeUser = $this->findUserByNumSecu($username);
                // nouveau user
                if (count($existeUser) == 0) {
//                    die('count($existeUser) =  0');
//                    var_dump($existeUser);
//                    die('count($existeUser) == 0');
                    $User = new User();
                    $User->setUserName($username);
                    $User->setIsActive(false);
                    $salt = uniqid(mt_rand());
                    $User->setSalt($salt); // Unique salt for user
                    $repRole = $db->getRepository('FrontBundle:Role');
                    $role_user = $repRole->findOneBy(array('role' => 'ROLE_USER'));

                    $User->addRole($role_user);
//                    $plain_password = $salary->getUser()->getPassword();
//                    $encoder = $this->container->get('security.encoder_factory')->getEncoder();
                    $password = hash('sha512', $salt . $plain_password);
//                     var_dump($password);
//                    die;
//                    $password = $encoder->encodePassword($plain_password, $salt);
                    $User->setPassword($password);

                    $db->persist($User);
                    $db->flush();

                    $user_id = $User->getId();

//                    $JsonResponse = new JsonResponse(array('user_id' => var_dump($user_id)));
//                    return $JsonResponse;
//                    var_dump('yessss');
//                     var_dump($user_id);
//                     die('flush + exist=0');
//                  
                }
                if (count($existeUser) > 0) {
//                    var_dump($existeUser);
//                    die('count($existeUser) > 0');
                    $UserExiste = $db->getRepository('FrontBundle:User')->find($existeUser[0]['id']);
                    $user_id = $UserExiste->getId();
                    if ($user_id != null) {
                        $array_fetch_salary_by = $this->fetchExisteSalaryBy($company_id, $user_id, $matricule_salary);
//                        $array_fetch_salary_by = $this->fetchExisteSalaryinEntity($company_id, $user_id);
//                        var_dump('<--------------->');
//                        var_dump($company_id);
//                        var_dump($user_id);
//                        var_dump($matricule_salary);
//                        var_dump(count($array_fetch_salary_by));
//                        var_dump($array_fetch_salary_by);
//                        die('count($existeUser) > 0');
                        if (count($array_fetch_salary_by) == 0) {

                            $getUser = $db->getRepository('FrontBundle:User')->find($user_id);
//                            var_dump($getUser);
//                            die('$array_fetch_salary_by = 0');
                            if ($getUser) {
                                $salary->setUser($getUser);

                                // Upload image
                                $file = $salary->getPhoto();

//                                var_dump($file);
//                                die('file + photo');
                                if (isset($file) && !empty($file)) {
                                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                                    //move the file to the directory where documents are stored
                                    $docDir = '/docs_sign/' . $this->container->getParameter('client_id') . '/pic';
                                    $file->move($docDir, $fileName);
                                    $salary->setPhoto($fileName);

//                                   $salary->getUser()->setVisibility(false);
                                }
                                $isPaper = $salary->getIsPaper();
                                $activateSended = $salary->getActivationSended();
                                $email = $salary->getEmailPerso();
                                if ($isPaper == false && $activateSended == true) {
                                    if (is_null($email)) {
                                        $email = $salary->getEmailPro();
                                    }
                                    if (isset($email) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
//                                     die('if');
                                        $unique = uniqid();
                                        $baseUrl = $request->getHttpHost();
                                        $lienActivation = 'https://' . $baseUrl . '' . $this->generateUrl('activate_salary', array('code' => $unique));
                                        $this->get('email_servies')->sendActivation($salary, $lienActivation);
//                                    die('email_servies');
                                        $rec = new ActivateSalary();
                                        $rec->setCode($unique);
                                        $rec->setDone(false);
                                        $rec->setUser($salary->getUser());
                                        $db->persist($rec);
                                        $salary->setActivationSended(true);
                                        $salary->getUser()->setIsActive(false);
//                                    $db->flush();
                                    }
                                }
                                $db->persist($salary);
                                $db->flush();
//                                die('flush');
                                $this->get('session')->getFlashBag()->add('info', 'Votre salarié est crée avec succès');
//                                die('getFlashBag add salary');
                                return $this->redirect($this->generateUrl('front_homepage'));
//                                $Response = new Response('insertion avec succée');
//                                return $Response;
                            }
                        }



//                   
                    }
                }
//                
            }
        }


        //$request->cookies $request->getSession()
//        var_dump($request->cookies);
//        var_dump($form->createView());
//        die('getSession');

        return $this->render('FrontBundle:Salary:add.html.twig', array('form' => $form->createView(), 'salary' => $salary));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function activateSendEmailAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $datax = $request->query->all();
        $id_salary = $datax['param'];
        $salary = $em->getRepository('FrontBundle:Salary')->find($id_salary);
        if ($salary) {
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
		$salary->getUser()->setIsActive(true);
                $em->flush();
	    }
            
        }
        $JsonResponse = new JsonResponse(array('tab' => $id_salary));
        return $JsonResponse;
    }
    
    
    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function demandeActivationSalaryAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $datax = $request->query->all();
        $id_salary = $datax['param'];
        $salary = $em->getRepository('FrontBundle:Salary')->find($id_salary);
        if ($salary) {
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
        $JsonResponse = new JsonResponse(array('tab' => $id_salary));
        return $JsonResponse;
    }
    

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function desactivateSendEmailAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $datax = $request->query->all();
        $id_salary = $datax['param'];
        $salary = $em->getRepository('FrontBundle:Salary')->find($id_salary);
        if ($salary) {
            $salary->setActivationSended(false);
            $salary->getUser()->setIsActive(false);
            $em->persist($salary);
            $em->flush();
        }
        $JsonResponse = new JsonResponse(array('tab' => $id_salary));
        return $JsonResponse;
    }

    
    
    public function existeSalaryAddInCompanyAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $nb = 0;
        $data = $request->query->all();
        $matricule = $data['param']; //'221548741'
        $company_id = $data['param1'];
        //$user_name = $data['param2'];
        $num_secu = $data['param2'];
        $user_name = substr($num_secu, 0, 13);
        $proxyObject = $em->getRepository('FrontBundle:User')->findOneBy(array('username' => $user_name));

        if ($proxyObject == null) {
            $nb = -1;
            $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 0, 'action' => 'existeSalaryAddInCompany'));
            return $JsonResponse;
        }

        if ($proxyObject) {
            $user_id = $proxyObject->getId();
            $array_existe_salary_by = $this->fetch2ExisteSalaryBy($company_id, $matricule);
//            $array_existe_salary_by = $this->fetchExisteSalaryBy($company_id, $user_id, $matricule);
            if (count($array_existe_salary_by) == 0) {

                $nb = 0;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 1, 'action' => 'existeSalaryAddInCompany'));
                return $JsonResponse;
            }

            if (count($array_existe_salary_by) >= 1) {

                $nb = 1;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 2, 'action' => 'existeSalaryAddInCompany'));
                return $JsonResponse;
            }
        }





//        $JsonResponse = new JsonResponse(array('matricule'=>$matricule,'company_id'=>$company_id,'user_name'=>$user_name,'salary_edit_id'=>$salary_edit_id));
//        $JsonResponse = new JsonResponse(array('tab' => $proxyObject->getId()));
//        return $JsonResponse;
    }

    public function salaryAddExisteMatriculeAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $nb = 0;
        $data = $request->query->all();
        $matricule = $data['param']; //'221548741'
        $company_id = $data['param1'];
        $user_name = $data['param2'];


//        $proxyObject = $em->getRepository('FrontBundle:User')->findOneBy(array('username' => $user_name));
//        $user_id = $proxyObject->getId();
        $array_existe_salary_by = $this->fetchExisteMatriculeyBy($company_id, $matricule);
        if (count($array_existe_salary_by) == 0) {

            $nb = 0;
            $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 1, 'action' => 'salaryAddExisteMatricule'));
            return $JsonResponse;
        }

        if (count($array_existe_salary_by) >= 1) {

            $nb = 1;
            $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 2, 'action' => 'salaryAddExisteMatricule'));
//            $JsonResponse = new JsonResponse(array('tab' => $array_existe_salary_by));
            return $JsonResponse;
        }



//        $JsonResponse = new JsonResponse(array('matricule'=>$matricule,'company_id'=>$company_id,'user_name'=>$user_name,'salary_edit_id'=>$salary_edit_id));
//        $JsonResponse = new JsonResponse(array('tab' => $proxyObject->getId()));
//        return $JsonResponse;
    }

    public function salaryAddExistNumSecuAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $nb = 0;
        $data = $request->query->all();
        $matricule = $data['param'];
        $company_id = $data['param1'];
        $user_name = $data['param2'];
        $num_secu = $data['param4'];

        $first13 = substr($num_secu, 0, 13);
        $proxyObject = $em->getRepository('FrontBundle:User')->findOneBy(array('username' => $first13));

        if ($proxyObject == null) {
            $nb = -1;
            $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 0, 'action' => 'salaryAddExistNumSecu'));
            return $JsonResponse;
        }

        if ($proxyObject) {
            $user_id = $proxyObject->getId();
            $array_existe_salary_by = $this->fetchExisteNumSecuBy($company_id, $user_id, $matricule, $num_secu);
            if (count($array_existe_salary_by) == 0) {

                $nb = 0;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 1, 'action' => 'salaryAddExistNumSecu'));
                return $JsonResponse;
            }

            if (count($array_existe_salary_by) >= 1) {

                $nb = 1;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 2, 'action' => 'salaryAddExistNumSecu'));
                return $JsonResponse;
            }
        }




//        $JsonResponse = new JsonResponse(array('matricule'=>$matricule,'company_id'=>$company_id,'user_name'=>$user_name,'salary_edit_id'=>$salary_edit_id));
//        $JsonResponse = new JsonResponse(array('tab' => $proxyObject->getId()));
//        return $JsonResponse;
    }

    public function ExisteSalaryInCompanyAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $nb = 0;
        $data = $request->query->all();
        $matricule = $data['param']; //'221548741'
        $company_id = $data['param1'];
//        $user_name = $data['param2'];
        $salary_edit_id = $data['param3'];
        $num_secu = $data['param2'];
        $user_name = substr($num_secu, 0, 13);
        $proxyObject = $em->getRepository('FrontBundle:User')->findOneBy(array('username' => $user_name));
        if ($proxyObject == null) {


            //verif if numsecu existe dans table user, et si il correspend a un autre salarier
            //verif si num secu correspend a un autre salarier: id_salarier_courant != id_salary_by_ce num secu
            $nb = -1;
            $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 0, 'action' => 'salary_update_ExisteSalaryInCompany'));
            return $JsonResponse;
        }


        if ($proxyObject) {
            $user_id = $proxyObject->getId(); //fetch2ExisteSalaryBy
//            $array_existe_salary_by = $this->fetchExisteSalaryBy($company_id, $user_id, $matricule);
            $array_existe_salary_by = $this->fetch2ExisteSalaryBy($company_id, $matricule);
            if (count($array_existe_salary_by) == 0) {
                $nb = 0;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 1, 'action' => 'salary_update_ExisteSalaryInCompany'));
                return $JsonResponse;
            }
            if (count($array_existe_salary_by) == 1) {



                if ($array_existe_salary_by[0]['id'] != $salary_edit_id) { //$salary_edit_id
                    $nb = 1;
                    $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 2, 'action' => 'salary_update_ExisteSalaryInCompany'));
                    return $JsonResponse;
                } elseif ($array_existe_salary_by[0]['id'] == $salary_edit_id) {   // $salary_edit_id
                    $nb = 0;
                    $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 3, 'action' => 'salary_update_ExisteSalaryInCompany'));
                    return $JsonResponse;
                }
//            $JsonResponse = new JsonResponse(array('nb' => $array_existe_salary_by[0]['id']));
//            return $JsonResponse;
            }
            if (count($array_existe_salary_by) > 1) {

                $nb = 1;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 4, 'action' => 'salary_update_ExisteSalaryInCompany'));
                return $JsonResponse;
            }
        }



//        $JsonResponse = new JsonResponse(array('matricule'=>$matricule,'company_id'=>$company_id,'user_name'=>$user_name,'salary_edit_id'=>$salary_edit_id));
//        $JsonResponse = new JsonResponse(array('tab' => $proxyObject->getId()));
//        return $JsonResponse;
    }

    public function existeMatriculeInCompanyAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $nb = 0;
        $data = $request->query->all();
        $matricule = $data['param']; //'221548741'
        $company_id = $data['param1'];
        $user_name = $data['param2'];
        $salary_edit_id = $data['param3'];

//        $proxyObject = $em->getRepository('FrontBundle:User')->findOneBy(array('username' => $user_name));
//        $user_id = $proxyObject->getId();
        $array_existe_salary_by = $this->fetchExisteMatriculeyBy($company_id, $matricule);
        if (count($array_existe_salary_by) == 0) {

            $nb = 0;
            $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 1, 'action' => 'salary_update_existeMatriculeInCompany'));
            return $JsonResponse;
        }
        if (count($array_existe_salary_by) == 1) {

//            if ($array_existe_salary_by[0]['company_id'] != $company_id) {          //$salary_edit_id,$array_existe_salary_by[0]['id']
//                $nb = 1;
//                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 2));
//                return $JsonResponse;
//            } 

            if (($array_existe_salary_by[0]['company_id'] == $company_id) && ($array_existe_salary_by[0]['id'] == $salary_edit_id)) {        //$salary_edit_id,$array_existe_salary_by[0]['id']
                $nb = 0;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 2, 'action' => 'salary_update_existeMatriculeInCompany'));
                return $JsonResponse;
            }
            if (($array_existe_salary_by[0]['company_id'] == $company_id) && ($array_existe_salary_by[0]['id'] != $salary_edit_id)) {        //$salary_edit_id,$array_existe_salary_by[0]['id']
                $nb = 1;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 3, 'action' => 'salary_update_existeMatriculeInCompany'));
                return $JsonResponse;
            }
//            $JsonResponse = new JsonResponse(array('nb' => $array_existe_salary_by[0]['id']));
//            return $JsonResponse;
        }
        if (count($array_existe_salary_by) > 1) {

            $nb = 1;
            $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 4, 'action' => 'salary_update_existeMatriculeInCompany'));
//            $JsonResponse = new JsonResponse(array('tab' => $array_existe_salary_by));
            return $JsonResponse;
        }



//        $JsonResponse = new JsonResponse(array('matricule'=>$matricule,'company_id'=>$company_id,'user_name'=>$user_name,'salary_edit_id'=>$salary_edit_id));
//        $JsonResponse = new JsonResponse(array('tab' => $proxyObject->getId()));
//        return $JsonResponse;
    }

    public function existeNumSecuInCompanyAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $nb = 0;
        $data = $request->query->all();
        $matricule = $data['param']; //'221548741'
        $company_id = $data['param1'];
        $user_name = $data['param2'];
        $salary_edit_id = $data['param3'];
        $num_secu = $data['param4'];

        $first13 = substr($num_secu, 0, 13);
        $proxyObject = $em->getRepository('FrontBundle:User')->findOneBy(array('username' => $first13));

        //verif if numsecu existe dans table user, et si il correspend a un autre salarier
        //verif si num secu correspend a un autre salarier: id_salarier_courant != id_salary_by_ce num secu
        //faire update sur les tous num secu attribuer a ce salarier
        if ($proxyObject == null) {
            $nb = -1;
            $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 0, 'action' => 'salary_update_existeNumSecuInCompany'));
            return $JsonResponse;
        }

        if ($proxyObject) {
            $user_id = $proxyObject->getId();
            $array_existe_salary_by = $this->fetchExisteNumSecuBy($company_id, $user_id, $matricule, $num_secu);
            if (count($array_existe_salary_by) == 0) {

                $nb = 0;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 1, 'action' => 'salary_update_existeNumSecuInCompany'));
                return $JsonResponse;
            }
            if (count($array_existe_salary_by) == 1) {

                if ($array_existe_salary_by[0]['id'] == $salary_edit_id) {
                    $nb = 0;
                    $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 2, 'action' => 'salary_update_existeNumSecuInCompany'));
                    return $JsonResponse;
                }
                if ($array_existe_salary_by[0]['id'] != $salary_edit_id) {

                    $nb = 1;
                    $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 3, 'action' => 'salary_update_existeNumSecuInCompany'));
                    return $JsonResponse;
                }
//            $JsonResponse = new JsonResponse(array('nb' => $array_existe_salary_by[0]['id']));
//            return $JsonResponse;
            }
            if (count($array_existe_salary_by) > 1) {

                $nb = 1;
                $JsonResponse = new JsonResponse(array('nb' => $nb, 'cas' => 4, 'action' => 'salary_update_existeNumSecuInCompany'));
                return $JsonResponse;
            }
        }




//        $JsonResponse = new JsonResponse(array('matricule'=>$matricule,'company_id'=>$company_id,'user_name'=>$user_name,'salary_edit_id'=>$salary_edit_id));
//        $JsonResponse = new JsonResponse(array('tab' => $proxyObject->getId()));
//        return $JsonResponse;
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function editAction($id) {


//        die(' editAction ');

        $request = Request::createFromGlobals();

        $db = $this->getDoctrine()->getManager();
        $repSalary = $db->getRepository('FrontBundle:Salary');
//        $repUser = $db->getRepository('FrontBundle:User');

        $salary = $repSalary->find($id);
        $user_id = $salary->getUser()->getId();
//         var_dump($user_id);
//        die('$user_id');
        $array_user = $this->getUserById($user_id);

        $User = $array_user[0];
        //$User['username']
//        var_dump($User['username']);
//        die('icici');
//        $form = $this->createForm(new SalaryEditForm(), $salary, array(
//            'cascade_validation' => true
//        ));

        $form = $this->createForm(new SalaryEditForm(), $salary);
        $old_pic = $salary->getPhoto();
        $old_password = $salary->getUser()->getPassword();
        if ($request->getMethod() == 'POST') {
//            die(' POST ');
            $delete_tof = $request->get('remove_img');

            $form->bind($request);
            if ($form->isValid()) {
//                $username_old = $salary->getUser()->getUsername();
//                $username = $form->get('extra_user_name_edit')->getData();
//                $pwd = $form->get('extra_user_pwd_edit')->getData();
//                var_dump($username_old);
//                var_dump($username);
//                var_dump($pwd);
//                die('edit salary + POST + Valid');
                $UserEdit = NULL;
                $objUser = NULL;

                $user_id_edit = null;
                $user_id_old = $salary->getUser()->getId();

                $username_old = $salary->getUser()->getUsername();

                $username = $form->get('extra_user_name_edit')->getData();
                $pwd = $form->get('extra_user_pwd_edit')->getData();
                $company_id = $form->get('extra_company_salary_edit')->getData();
                $matricule_salary = $form->getData()->getMatricule();

//                var_dump($username_old);
//                var_dump($username);
//                var_dump($pwd);
//                var_dump($company_id);
//                var_dump($matricule_salary);
//                die('icicici');

                $proxyObject = $db->getRepository('FrontBundle:User')->findOneBy(array('username' => $username));
                if ($proxyObject == null) {
                    //edit login + pwd if changed
                    if (($username_old != $username)) {
                        $existe_Userby_numsecu = $this->findUserByNumSecu($username);
                        if (count($existe_Userby_numsecu) == 0) {

                            $objt_user_edit = $db->getRepository('FrontBundle:User')->find($user_id_old);
                            if ($objt_user_edit) {
                                $objt_user_edit->setUsername($username);
                                // ici il manque pwd pour editer
                                $db->flush($objt_user_edit);

//                                die('edit login + pwd if changed');
                            }
                        }
                    }
                }

                if ($proxyObject) {
                    // 1 -> get the proxy object class name
                    $proxy_class_user = get_class($proxyObject);
                    // 2 -> get the real object class name
                    $class_user = $db->getClassMetadata($proxy_class_user)->rootEntityName;
                    // 3 -> get the real object
                    $objUser = $db->find($class_user, $proxyObject->getId());
                    if ($objUser) {
                        $user_id_edit = $objUser->getId(); //$form->getData()->getUser()->getId();
                    }
                }
//                var_dump($form->getData());
                $num_secu_salary = $form->getData()->getNumSecu();
                //$first13 = substr($num_secu_salary, 0, 13);
                // si elle a change de numsecu
                $existeUser = $this->findUserByNumSecu($username);
                $allSalary_by_num_secu = $this->findAllSalaryBy($user_id_old);
                if (count($allSalary_by_num_secu) > 0) {

                    foreach ($allSalary_by_num_secu as $key => $value) {
                        $Salary_for_edit_ns = $db->getRepository('FrontBundle:Salary')->find($value['id']);
                        $Salary_for_edit_ns->setNumSecu($num_secu_salary);
                        $db->persist($Salary_for_edit_ns);
                    }
                    $db->flush();
                }
                if (count($existeUser) > 0) {
//                    die('count($existeUser) > 0 + update salary');
                    if ($existeUser[0]['id'] == $user_id_edit) {
                        $salary_incompany = false;
                        $UserEdit = $db->getRepository('FrontBundle:User')->find($existeUser[0]['id']);
                        $user_id_edit = $UserEdit->getId();
                        if ($user_id_edit != null) {
                            $CompanyEdit = $db->getRepository('FrontBundle:Company')->find($company_id);

                            if ($CompanyEdit) {

                                if (isset($pwd) && !empty($pwd)) {
//                                    var_dump($pwd);
//                                    die('pwd');

                                    $new_salt = uniqid(mt_rand());
                                    $salary->getUser()->setSalt($new_salt); // Unique salt for user
                                    // Set encrypted password
                                    $encoder = $this->container->get('security.encoder_factory')
                                            ->getEncoder($salary->getUser());
//                                    $password = $encoder->encodePassword($salary->getUser()->getPassword(), $salary->getUser()->getSalt());
                                    $password = $encoder->encodePassword($pwd, $new_salt);
                                    $salary->getUser()->setPassword($password);
                                } else {
                                    $salary->getUser()->setPassword($old_password);
                                }
                                // Upload image
                                $file = $salary->getPhoto();
                                $docDir = '/docs_sign/' . $this->container->getParameter('client_id') . '/pic';
                                if (isset($file) && !empty($file)) {
                                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                                    //move the file to the directory where documents are stored

                                    $file->move($docDir, $fileName);
                                    $salary->setPhoto($fileName);
                                    if ($delete_tof == 1) {
                                        unlink($docDir . '/' . $old_pic);
                                    }
                                } else {
                                    if ($delete_tof == 1) {
                                        unlink($docDir . '/' . $old_pic);
                                        $salary->setPhoto(null);
                                    } else {
                                        $salary->setPhoto($old_pic);
                                    }
                                }

//                                var_dump($CompanyEdit);
//                                die('flush salary');
                                $salary->setCompany($CompanyEdit);
                                $salary->setNumSecu($num_secu_salary);


                                $isPaper = $salary->getIsPaper();
                                $activateSended = $salary->getActivationSended();
                                $email = $salary->getEmailPerso();
                                if ($isPaper == false && $activateSended == true) {
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
                                        $db->persist($rec);
                                        $salary->setActivationSended(true);
                                        $salary->getUser()->setIsActive(false);
                                    }
                                }
                                $db->persist($salary);
                                $db->flush();
//                                die('flush salary');
                                $this->get('session')->getFlashBag()->add('info', 'Votre Salarié a été modifié avec succès');
                                return $this->redirect($this->generateUrl('front_homepage'));
//                                $Response = new Response('update avec succée');
//                                return $Response;
                            }
                        }
                    }
                }


//                //ici si user se trompe et veux changer le num secu
//                if (count($existeUser) == 0) {
//                    die('count($existeUser) == 0');
//                    $update_user = $db->getRepository('FrontBundle:User')->find($user_id_edit);
//                    $update_user->setUserName($username);
//                    $db->flush($update_user);
//                }
            }
        }

        //die('icicici');
//        return $this->render('FrontBundle:Salary:edit.html.twig', array('form' => $form->createView(),
//                    'ids' => $salary->getId(), 'salary' => $salary, 'user' => $User));

        $proxyObjectCompany = $salary->getCompany();
        $proxy_class_company = get_class($proxyObjectCompany);
        // 2 -> get the real object class name
        $class_company = $db->getClassMetadata($proxy_class_company)->rootEntityName;
        // 3 -> get the real object
        $objCompany = $db->find($class_company, $proxyObjectCompany->getId());

        return $this->render('FrontBundle:Salary:edit.html.twig', array('form' => $form->createView(),
                    'ids' => $salary->getId(), 'salary' => $salary, 'user' => $User, 'company_id' => $objCompany->getId()));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function removeAction($id) {

        $salary = $this->getDoctrine()
                ->getRepository('FrontBundle:Salary')
                ->find($id);

        if (!$salary) {
            throw $this->createNotFoundException(
                    'Aucun utilisateur trouvé pour cet id : ' . $id
            );
        }
        $em = $this->getDoctrine()->getEntityManager();

        $repDoc = $em->getRepository('FrontBundle:Document');
        $repSignedDoc = $em->getRepository('FrontBundle:SignedDoc');
        $repActiv = $em->getRepository('FrontBundle:ActivateSalary');
        // select all documents
        $sdocs = $repSignedDoc->findBy(array('salary' => $salary));
        // Select all signed documents
        $docs = $repDoc->findBy(array('salary' => $salary));
        $user = $salary->getUser();
        $activation = $repActiv->findOneBy(array('user' => $user));
        try {
            $em->beginTransaction();
            foreach ($sdocs as $sdoc) {
                $em->remove($sdoc);
            }
            // Remove all documents
            foreach ($docs as $doc) {
                $em->remove($doc);
            }
            // Remove salary
            $em->remove($salary);
            // Remove activation
            if (is_object($activation)) {
                $em->remove($activation);
            }
            // Remove the User
            $em->remove($user);
            $em->flush();
            $em->commit();


            $this->get('session')->getFlashBag()->add('info', 'La fiche du salarié ' . $salary->getNom() . ' ' . $salary->getPrenom() . ' a été supprimé avec succès');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->get('session')->getFlashBag()->add('erreur', 'Impossible de supprimer cette fiche');
            $em->rollback();
            $em->close();
        }

        return $this->redirect($this->generateUrl("front_homepage"));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function enableAction($id) {


        $salary = $this->getDoctrine()
                ->getRepository('FrontBundle:Salary')
                ->find($id);

//        var_dump($salary);
//        die('icic');
        if (!$salary) {
            throw $this->createNotFoundException(
                    'Aucun utilisateur trouvé pour cet id : ' . $id
            );
        }
        $em = $this->getDoctrine()->getEntityManager();
        $user = $salary->getUser();
//        var_dump($user);
//        die('icic');
        try {
//            $em->beginTransaction();
            $user->setIsActive(true);
            $em->flush($user);
//             die('flush');
//            $em->commit();
            $this->get('session')->getFlashBag()->add('info', 'La fiche du salarié ' . $salary->getNom() . ' ' . $salary->getPrenom() . ' a été activé avec succès');
            //send informational email

            $this->container->get('email_servies')->sendAfterActivation($salary);
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->get('session')->getFlashBag()->add('erreur', 'Impossible d\'activer cette fiche');
//            $em->rollback();
//            $em->close();
        }

        return $this->redirect($this->generateUrl("front_homepage"));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function disableAction($id) {

        $salary = $this->getDoctrine()
                ->getRepository('FrontBundle:Salary')
                ->find($id);

        if (!$salary) {
            throw $this->createNotFoundException(
                    'Aucun utilisateur trouvé pour cet id : ' . $id
            );
        }
        $em = $this->getDoctrine()->getEntityManager();
        $user = $salary->getUser();
        try {
            $em->beginTransaction();
            $user->setIsActive(false);
            $em->flush();
            $em->commit();


            $this->get('session')->getFlashBag()->add('info', 'La fiche du salarié ' . $salary->getNom() . ' ' . $salary->getPrenom() . ' a été activé avec succès');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->get('session')->getFlashBag()->add('erreur', 'Impossible d\'activer cette fiche');
            $em->rollback();
            $em->close();
        }

        return $this->redirect($this->generateUrl("front_homepage"));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function docsAction($id) {

        $db = $this->getDoctrine()->getManager();
        $salary = $db->getRepository('FrontBundle:Salary')->find($id);
        $repCat = $db->getRepository('FrontBundle:Category');
        $repDocs = $db->getRepository('FrontBundle:Document');
        $cats = $repCat->findAll();
        $tree = array();
        foreach ($cats as $cat) {

            $docs = $repDocs->findBy(array('salary' => $salary, 'category' => $cat));
            $tree[] = array($cat, $docs);
        }
        return $this->render('FrontBundle:Salary:docs.html.twig', array('tree' => $tree, 'salary' => $salary));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function addDocAction($id) {
        $doc = new Document();
        $request = Request::createFromGlobals();
        $db = $this->getDoctrine()->getManager();
        $salary = $db->getRepository('FrontBundle:Salary')->find($id);
        $form = $this->createForm(new DocumentForm(), $doc, array(
            'cascade_validation' => true
        ));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($doc->getDoc()->guessExtension() != 'pdf' && $doc->getSpecialDoc()) {
                $this->get('session')->getFlashBag()->add('erreur', 'Le document à signer doit être impérativement de type PDF');
                return $this->render('FrontBundle:Salary:add_doc.html.twig', array('form' => $form->createView(), 'ids' => $salary->getId()));
            } elseif (is_null($salary->getTelephonePerso()) && $doc->getSpecialDoc()) {
                $this->get('session')->getFlashBag()->add('erreur', 'Le signataire doit avoir un numéro de téléphone personnel.');
                return $this->render('FrontBundle:Salary:add_doc.html.twig', array('form' => $form->createView(), 'ids' => $salary->getId()));
            }
            if ($form->isValid()) {

                $file = $doc->getDoc();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                //move the file to the directory where documents are stored
                $docDir = '/docs_sign/' . $this->container->getParameter('client_id') . '/';
                $file->move($docDir, $fileName);
                $doc->setDoc($fileName);
                $doc->setSalary($salary);
                $salary->setIsDoc(true);
                $db->persist($doc);
                $db->flush();
                //send informational email

                if ($doc->getSpecialDoc()) {
                    $this->container->get('email_servies')->getHtmlDocSign($salary);
                } else {
                    $this->container->get('email_servies')->getHtmlDoc($salary);
                }



                $this->get('session')->getFlashBag()->add('info', 'Le document est ajouté avec succès');
                return $this->redirect($this->generateUrl('front_salary_docs', array('id' => $id)));
            } else {
                //@TODO Message d'erreur à ajouter
            }
        }

        return $this->render('FrontBundle:Salary:add_doc.html.twig', array('form' => $form->createView(), 'ids' => $salary->getId()));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function removeDocAction($id) {
        $idsalary = $id;
        $db = $this->getDoctrine()->getManager();
        $salary = $this->getDoctrine()
                ->getRepository('FrontBundle:Salary')
                ->find($idsalary);
        $repDocs = $db->getRepository('FrontBundle:Document');
        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'POST') {

            $selected = $request->request->get('selected_values');
            $ids = explode(',', $selected);

            try {
                foreach ($ids as $id) {
                    $doc = $repDocs->find($id);
                    if (is_object($doc)) {
                        $rfile = '/docs_sign/' . $this->container->getParameter('client_id') . '/' . $doc->getDoc();
                        $db->remove($doc);
                        $db->flush();
                        unlink($rfile);
                    }
                }
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('erreur', 'Un problème est survenu lors de la suppression des documents sélectionnés !');
                return $this->redirect($this->generateUrl('front_salary_docs', array('id' => $id)));
            }

            $docs = $repDocs->findBy(array('salary' => $salary));
            if (count($docs) > 0) {
                // set docs = true
                $salary->setIsDoc(true);
            } else {
                $salary->setIsDoc(false);
            }
            $db->flush();
            $this->get('session')->getFlashBag()->add('info', 'La suppression des documents est effectuée avec succès ');
        }

        return $this->redirect($this->generateUrl('front_salary_docs', array('id' => $idsalary)));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function visibilityDocAction($id) {
        $idsalary = $id;
        $db = $this->getDoctrine()->getManager();
        $repDocs = $db->getRepository('FrontBundle:Document');
        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'POST') {

            $selected = $request->request->get('selected_values');
            $ids = explode(',', $selected);

            try {
                foreach ($ids as $id) {
                    $doc = $repDocs->find($id);
                    if (is_object($doc)) {
                        if ($doc->getVisibility() == true) {
                            $doc->setVisibility(false);
                        } else {
                            $doc->setVisibility(true);
                        }
                        $db->persist($doc);
                    }
                }
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('erreur', 'Un problème est survenu lors du changement de la visibilité des documents sélectionnés !');
                return $this->redirect($this->generateUrl('front_salary_docs', array('id' => $id)));
            }
            $db->flush();
            $this->get('session')->getFlashBag()->add('info', 'Le changement de la visibilité des documents a été effectué avec succès ');
        }

        return $this->redirect($this->generateUrl('front_salary_docs', array('id' => $idsalary)));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function profileAction($id) {
//        var_dump($this->get('security.context')->isGranted('ROLE_RH'));

        $db = $this->getDoctrine()->getManager();
        $salary = $this->getDoctrine()
                ->getRepository('FrontBundle:Salary')
                ->find($id);


        $bulletins = $this->getDoctrine()
                ->getRepository('FrontBundle:SignedDoc')
                ->findBy(array('salary' => $salary), array('id' => 'desc'));
        $orderBulletins = array();
        foreach ($bulletins as $bu) {
            $orderBulletins[$bu->getYear()][] = $bu;
        }

        ksort($orderBulletins);
        $curYear = date('Y');

        $repCat = $db->getRepository('FrontBundle:Category');
        $repDocs = $db->getRepository('FrontBundle:Document');
        $repCoDocs = $db->getRepository('FrontBundle:CoDoc');

        $myCompany = $salary->getCompany();

        $cats = $repCat->findAll();

        $tree = array();
        $codocs = NULL;
        if ($myCompany) {
            $codocs = $myCompany->getCodocs();
        }

        // Get all docs to sign
        $sdocs = $repDocs->findBy(array('visibility' => true, 'specialDoc' => true, 'specialSigned' => false, 'salary' => $salary));
        foreach ($cats as $cat) {

            $docs = $repDocs->findBy(array('salary' => $salary, 'category' => $cat));
            $tree[] = array($cat, $docs);
        }


        return $this->render('FrontBundle:Salary:profile.html.twig', array('salary' => $salary, 'bulletins' => $orderBulletins, 'tree' => $tree, 'codocs' => $codocs, 'sdocs' => $sdocs, 'curyear' => $curYear));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function bulletinObseletAction(Request $request) {

        //bulletinObselet
        $em = $this->getDoctrine()->getManager();
        $data = $this->getRequest()->query->all();
        $signedDoc = $data['param'];
        $salaryid = $data['salaryid'];
        $salary = $em->getRepository('FrontBundle:Salary')->find($salaryid);
//        $salary = 
        $month = '';
        $year = '';
        $bulletins = $this->getDoctrine()->getRepository('FrontBundle:SignedDoc')->find($signedDoc);
        if ($bulletins) {
            $month = $bulletins->getMonth();
            $year = $bulletins->getYear();
            $bulletins->setObsolete(true);
            $em->flush($bulletins);
        }
//        $this->container->get('email_servies')->getHtmlBulletinObselet($salary, $month, $year);
//        $this->generateUrl('front_profile_salary', array('id' => $salaryid));
        $response = new Response('définir comme obsolete');
        return $response;
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function sendMailbulletinObseletAction(Request $request) {

        //bulletinObselet
        $em = $this->getDoctrine()->getManager();
        $data = $this->getRequest()->query->all();
        $signedDoc = $data['param'];
        $salaryid = $data['salaryid'];
        $salary = $em->getRepository('FrontBundle:Salary')->find($salaryid);
        $month = '';
        $year = '';
        $bulletins = $this->getDoctrine()->getRepository('FrontBundle:SignedDoc')->find($signedDoc);
        if ($bulletins) {
            $month = $bulletins->getMonth();
            $year = $bulletins->getYear();
        }
        $this->container->get('email_servies')->getHtmlBulletinObselet($salary, $month, $year);
        $response = new Response('send mail for obselet bulletin');
//        $response = $signedDoc ;
        return $response;
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function sendMailbulletinNonObseletAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $data = $this->getRequest()->query->all();
        $signedDoc = $data['param'];
        $salaryid = $data['salaryid'];
        $salary = $em->getRepository('FrontBundle:Salary')->find($salaryid);
        $month = '';
        $year = '';
        $bulletins = $this->getDoctrine()->getRepository('FrontBundle:SignedDoc')->find($signedDoc);
        if ($bulletins) {
            $month = $bulletins->getMonth();
            $year = $bulletins->getYear();
        }
        $this->container->get('email_servies')->getHtmlBulletinObselet($salary, $month, $year);
        $response = new Response('send mail for non obselet bulletin');
        return $response;
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function bulletinNonObseletAction(Request $request) {

        //bulletinNonObselet
        $data = $this->getRequest()->query->all();
        $signedDoc = $data['param'];
        $salaryid = $data['salaryid'];
        $em = $this->getDoctrine()->getManager();
        $bulletins = $this->getDoctrine()->getRepository('FrontBundle:SignedDoc')->find($signedDoc);
        if ($bulletins) {
            $bulletins->setObsolete(false);
            $em->flush($bulletins);
        }
        $response = new Response('définir comme non obsolete');
        return $response;
    }

    /**
     * @Secure(roles="ROLE_RH,ROLE_RH_LIMITE, ROLE_USER")
     */
    public function downloadSignedDocAction($id) {
        $db = $this->getDoctrine()->getManager();

        // If it's my document
        $repSignedDoc = $db->getRepository('FrontBundle:SignedDoc');
        $sdoc = $repSignedDoc->find($id);
        $current_user = $this->get('security.context')->getToken()->getUser();

        if ($sdoc->getSalary()->getUser()->getId() == $current_user->getId() || $this->get('security.context')->isGranted('ROLE_RH')) {
            $certSign = $this->get('cert_sign.server');
            $data = $certSign->getDocument($sdoc->getSignature(), $sdoc->getRecord());
            $response = new Response($data, 200);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set("Content-disposition", 'attachment; filename=fiche_de_paie_' . $sdoc->getMonth() . '_' . $sdoc->getYear() . '.pdf');
        } else {
            $this->get('session')->getFlashBag()->add('erreur', 'Impossible de répondre à votre demande');

            $response = new RedirectResponse($this->get('router')->generate('front_my_profile'));
        }
        return $response;
    }

    /**
     * @Secure(roles="ROLE_RH,ROLE_RH_LIMITE, ROLE_USER")
     */
    public function downloadDocAction($id) {
        $db = $this->getDoctrine()->getManager();
        $repDoc = $db->getRepository('FrontBundle:Document');
        $doc = $repDoc->find($id);
        $current_user = $this->get('security.context')->getToken()->getUser();
        if ($doc->getSalary()->getUser()->getId() == $current_user->getId() || $this->get('security.context')->isGranted('ROLE_RH')) {
            $data = file_get_contents('/docs_sign/' . $this->container->getParameter('client_id') . '/' . $doc->getDoc());
            $response = new Response($data, 200);
            $response->headers->set("Content-disposition", 'attachment; filename=doc_' . $doc->getDoc());
        } else {
            $this->get('session')->getFlashBag()->add('erreur', 'Impossible de répondre à votre demande');

            $response = new RedirectResponse($this->get('router')->generate('front_my_profile'));
        }
        return $response;
    }

    /**
     * @Secure(roles="ROLE_RH,ROLE_RH_LIMITE, ROLE_USER")
     */
    public function picSalaryAction($id) {
        $db = $this->getDoctrine()->getManager();
        $repSalary = $db->getRepository('FrontBundle:Salary');
        $salary = $repSalary->find($id);
        if (is_object($salary)) {
            $file = '/docs_sign/' . $this->container->getParameter('client_id') . '/pic' . '/' . $salary->getPhoto();
            $response = new Response(file_get_contents($file), 200);
            $response->headers->set('Content-Type', mime_content_type($file));
            return $response;
        } else {
            return null;
        }
    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function myProfileAction() {

        $current_user = $this->get('security.context')->getToken()->getUser();
        $user_id = $current_user->getId();
//        var_dump($user_id);
//        die('$user_id');
        $array_salary = array();
        $index_salary = 0;
        $array_fetch_salary_by = $this->findSalaryBy($user_id);



//        var_dump($array_fetch_salary_by);
//        die('$array_fetch_salary_by');
        foreach ($array_fetch_salary_by as $key => $value) {
            $All_Salary = $this->getDoctrine()
                    ->getRepository('FrontBundle:Salary')
                    ->find($value['id']);
            $array_salary[$index_salary] = $All_Salary;
            $index_salary ++;
        }


        $db = $this->getDoctrine()->getManager();
//        $salary = $this->getDoctrine()
//                ->getRepository('FrontBundle:Salary')
//                ->findOneBy(array('user' => $current_user));



        $bulletins = array();
        $index_bulletin = 0;
//        var_dump($array_salary);
//        die('$array_salary');
        foreach ($array_salary as $key => $value) {
            $bulletins[$index_bulletin] = $this->getDoctrine()
                    ->getRepository('FrontBundle:SignedDoc')
                    ->findBy(array('salary' => $value, 'obsolete' => false), array('id' => 'desc'));
            $index_bulletin ++;
        }

//        var_dump($bulletins);
//        die('$bulletins');
//        $bulletins = $this->getDoctrine()
//                ->getRepository('FrontBundle:SignedDoc')
//                ->findBy(array('salary' => $salary, 'obsolete' => false), array('id' => 'desc'));
        $orderBulletins = array();
        foreach ($bulletins as $bu) {
            foreach ($bu as $key => $value) {
                $orderBulletins[$value->getYear()][] = $value;
            }
            //$orderBulletins[$bu->getYear()][] = $bu;
        }
//        var_dump($orderBulletins);
//        die('$orderBulletins');

        ksort($orderBulletins);
        $curYear = date('Y');
//        var_dump($orderBulletins);
//        die('order bulletin + ksort');

        $repCat = $db->getRepository('FrontBundle:Category');
        $repDocs = $db->getRepository('FrontBundle:Document');
        $repCoDocs = $db->getRepository('FrontBundle:CoDoc');

        $cats = $repCat->findAll();
        $tree = array();



        $myCompany = array();
        $index_company = 0;
        foreach ($array_fetch_salary_by as $key => $value) {

            $All_Salary = $this->getDoctrine()
                    ->getRepository('FrontBundle:Salary')
                    ->find($value['id']);

            $myCompany[$index_company] = $All_Salary->getCompany();
            $index_company ++;
//            var_dump($value);
//            die('$value');
//            $AllCompany_bySalary = $db->getRepository('FrontBundle:Company')->find($value['company_id']);
        }


//        $codocs = array();
//        $index_codocs = 0;
//        foreach ($myCompany as $key => $value) {
//
//            $codocs[$index_codocs] = $value->getCodocs();
//            $index_codocs ++;
//        }
//        $myCompany = $salary->getCompany();
//        $codocs = $myCompany->getCodocs();
//        $mycodocs = array();
//        foreach ($codocs as $codoc) {
//            if ($codoc->getToSign() != true) {
//                $mycodocs[] = $codoc;
//            }
//        }
        $mycodocs = NULL;
        $sdocs = NULL;

        // Get all docs to sign
//        $sdocs = $repDocs->findBy(array('visibility' => true, 'specialDoc' => true, 'specialSigned' => false, 'salary' => $salary));


        if (count($cats) > 0) {
            foreach ($cats as $cat) {
                foreach ($array_salary as $key => $value) {
                    $salary = $value;
                    $docs = $repDocs->findBy(array('salary' => $salary, 'category' => $cat, 'visibility' => true));
                    $tree[] = array($cat, $docs);
                }
            }
        } else {

            $tree = NULL;
        }


        $salary = $array_salary[0];
//        var_dump($orderBulletins);
//        die('$orderBulletins');
        //$curYear
        //$tree
        //$mycodocs
        //$sdocs
        return $this->render('FrontBundle:Salary:myprofile.html.twig', array('salary' => $salary, 'bulletins' => $orderBulletins, 'tree' => $tree, 'codocs' => $mycodocs, 'sdocs' => $sdocs, 'curyear' => $curYear));
//        return $this->render('FrontBundle:Salary:profile.html.twig', array('salary' => $salary, 'bulletins' => $orderBulletins, 'tree' => $tree, 'codocs' => $mycodocs, 'sdocs' => $sdocs, 'curyear' => $curYear));
    }

    public function findSalaryBy($user_id) {
        // myProfileAction
        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".salary  ";
//        $query = "SELECT * FROM 3cexternpaie_fulloffice_db.salary  ";
        $query .= ' ';
        $query_where = "where  user_id ='" . $user_id . "'";
        $queryfinale = $query . $query_where;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findUserByNumSecu($num_secu) {
//        die('findUserByNumSecu + add + edit + salary');
        $db_name = $this->container->getParameter('database_name');
        //$query = "SELECT * FROM 3cexternpaie_fulloffice_db.user  ";
        $query = "SELECT * FROM " . $db_name . " " . ".user  ";
        $query .= ' ';
        $query_where = "where  username ='" . $num_secu . "' ";
        $queryfinale = $query . $query_where;


        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
//        var_dump($stmt->fetchAll());
//        die('findUserByNumSecu + add + edit + salary');
        return $stmt->fetchAll();
    }

    public function findAllSalaryBy($user_id) {

        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".salary  ";

//        $query = "SELECT * FROM 3cexternpaie_fulloffice_db.salary ";
        $query .= ' ';
        $query .= "where  user_id ='" . $user_id . "'";
        $query .= ' ';
        $queryfinale = $query;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function fetchExisteSalaryBy($company_id, $user_id, $matricule) {

        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".salary  ";

//        $query = "SELECT * FROM 3cexternpaie_fulloffice_db.salary ";
        $query .= ' ';
        $query_where = "where  company_id ='" . $company_id . "'";
        $query_where .= ' ';
        $query_and = "and  user_id ='" . $user_id . "'";
        $query_and .= ' ';
        $query_and .= "and  matricule ='" . $matricule . "'";
        $query_and .= ' ';
        $queryfinale = $query . $query_where . $query_and;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function fetch2ExisteSalaryBy($company_id, $matricule) {

        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".salary  ";


//        $query = "SELECT * FROM 3cexternpaie_fulloffice_db.salary ";
        $query .= ' ';
        $query .= "where  company_id ='" . $company_id . "'";
        $query .= ' ';
        $query .= "and  matricule ='" . $matricule . "'";
        $query .= ' ';
        $queryfinale = $query; //. $query_where . $query_and;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function fetchExisteNumSecuBy($company_id, $user_id, $matricule, $num_secu) {

        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".salary  ";


//        $query = "SELECT * FROM 3cexternpaie_fulloffice_db.salary ";
        $query .= ' ';
        $query_where = "where  company_id ='" . $company_id . "'";
        $query_where .= ' ';
        $query_and = "and  user_id ='" . $user_id . "'";
        $query_and .= ' ';
        $query_and .= "and  matricule ='" . $matricule . "'";
        $query_and .= ' ';
        $query_and .= "and  num_secu ='" . $num_secu . "'";
        $query_and .= ' ';
        $queryfinale = $query . $query_where . $query_and;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function fetchExisteMatriculeyBy($company_id, $matricule) {

        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".salary  ";



//        $query = "SELECT * FROM 3cexternpaie_fulloffice_db.salary ";
        $query .= ' ';
        $query .= "where  company_id ='" . $company_id . "'";
        $query .= ' ';
//        $query_and = "and  user_id ='" . $user_id . "'";
//        $query_and .= ' ';
        $query .= "and  matricule ='" . $matricule . "'";
        $query .= ' ';
        $queryfinale = $query; //. $query_where . $query_and;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function fetchExisteSalaryinEntity($company_id, $user_id) {
        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".salary  ";

//        $query = "SELECT * FROM 3cexternpaie_fulloffice_db.salary ";
        $query .= ' ';
        $query_where = "where  company_id ='" . $company_id . "'";
        $query_where .= ' ';
        $query_and = "and  user_id ='" . $user_id . "'";
        $query_and .= ' ';
//        $query_and .= "and  matricule ='" . $matricule . "'";
//        $query_and .= ' ';
        $queryfinale = $query . $query_where . $query_and;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLigneSalaryBy($company_id, $user_id, $matricule) {
        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT SELECT count(user_id) as user_id  FROM " . $db_name . " " . ".salary  ";

//        $query = "SELECT count(user_id) as user_id FROM 3cexternpaie_fulloffice_db.salary ";
        $query .= ' ';
        $query_where = "where  company_id ='" . $company_id . "'";
        $query_where .= ' ';
        $query_and = "and  user_id ='" . $user_id . "'";
        $query_and .= ' ';
        $query_and .= "and  matricule ='" . $matricule . "'";
        $query_and .= ' ';
        $queryfinale = $query . $query_where . $query_and;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserById($user_id) {

//        die('getUserById + edit salary');

        $db_name = $this->container->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".user  ";

//        $query = "SELECT * FROM 3cexternpaie_fulloffice_db.user ";
        $query .= ' ';
        $query_where = "where  id ='" . $user_id . "'";
        $query_where .= ' ';
        $queryfinale = $query . $query_where;

        $em = $this->container->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function myProfile2Action() {


////        var_dump($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED'));
////        die('ici role !!');
//        $current_user = $this->get('security.context')->getToken()->getUser();
//
//        $db = $this->getDoctrine()->getManager();
//        $salary = $this->getDoctrine()
//                ->getRepository('FrontBundle:Salary')
//                ->findOneBy(array('user' => $current_user));
//
//        $bulletins = $this->getDoctrine()
//                ->getRepository('FrontBundle:SignedDoc')
//                ->findBy(array('salary' => $salary, 'obsolete' => false), array('id' => 'desc'));
//        $orderBulletins = array();
//        foreach ($bulletins as $bu) {
//            $orderBulletins[$bu->getYear()][] = $bu;
//        }
//
//        ksort($orderBulletins);
//        $curYear = date('Y');
//
//        $repCat = $db->getRepository('FrontBundle:Category');
//        $repDocs = $db->getRepository('FrontBundle:Document');
//        $repCoDocs = $db->getRepository('FrontBundle:CoDoc');
//
//        $cats = $repCat->findAll();
//        $tree = array();
//        $myCompany = $salary->getCompany();
//        $codocs = $myCompany->getCodocs();
//
//        $mycodocs = array();
//        foreach ($codocs as $codoc) {
//            if ($codoc->getToSign() != true) {
//                $mycodocs[] = $codoc;
//            }
//        }
//
//        // Get all docs to sign
//        $sdocs = $repDocs->findBy(array('visibility' => true, 'specialDoc' => true, 'specialSigned' => false, 'salary' => $salary));
//
//        foreach ($cats as $cat) {
//
//            $docs = $repDocs->findBy(array('salary' => $salary, 'category' => $cat, 'visibility' => true));
//            $tree[] = array($cat, $docs);
//        }
//
//        return $this->render('FrontBundle:Salary:profile.html.twig', array('salary' => $salary, 'bulletins' => $orderBulletins, 'tree' => $tree, 'codocs' => $mycodocs, 'sdocs' => $sdocs, 'curyear' => $curYear));
    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function myPreferencesAction(Request $request) {
        $current_user = $this->get('security.context')->getToken()->getUser();

        $db = $this->getDoctrine()->getManager();
        $salary = $this->getDoctrine()
                ->getRepository('FrontBundle:Salary')
                ->findOneBy(array('user' => $current_user));
        $finalChoice = false;
        $var_choice = $this->container->getParameter('choice_salary');
        if ($var_choice == 1) {
            $finalChoice = true;
        }
        $form = $this->createForm(new MyPreferencesForm(array('choice_salary' => $finalChoice)), $salary, array(
            'cascade_validation' => true
        ));


        $old_pic = $salary->getPhoto();
        $old_password = $salary->getUser()->getPassword();
        if ($request->getMethod() == 'POST') {
            $delete_tof = $request->get('remove_img');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $plain_password = $salary->getUser()->getPassword();

                if (isset($plain_password) && !empty($plain_password)) {
                    $salary->getUser()->setIsActive(true);
                    $salary->getUser()->setSalt(uniqid(mt_rand())); // Unique salt for user
                    $salary->getUser()->setIsActive(true);
                    // Set encrypted password
                    $encoder = $this->container->get('security.encoder_factory')
                            ->getEncoder($salary->getUser());
                    $password = $encoder->encodePassword($salary->getUser()->getPassword(), $salary->getUser()->getSalt());
                    $salary->getUser()->setPassword($password);
                } else {
                    $salary->getUser()->setPassword($old_password);
                }

                // Upload image
                $file = $salary->getPhoto();
                $docDir = '/docs_sign/' . $this->container->getParameter('client_id') . '/pic';
                if (isset($file) && !empty($file)) {
                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                    //move the file to the directory where documents are stored

                    $file->move($docDir, $fileName);
                    $salary->setPhoto($fileName);
                    if ($delete_tof == 1) {
                        unlink($docDir . '/' . $old_pic);
                    }
                } else {
                    if ($delete_tof == 1) {
                        unlink($docDir . '/' . $old_pic);
                        $salary->setPhoto(null);
                    } else {
                        $salary->setPhoto($old_pic);
                    }
                }
                // End Upload image
                $db->persist($salary);
                $db->flush();


                $this->get('session')->getFlashBag()->add('info', 'Votre choix a été sauvegardé avec succès');
                return $this->redirect($this->generateUrl('front_my_profile'));
            } else {
                //@TODO Message d'erreur à ajouter
            }
        }

        return $this->render('FrontBundle:Salary:preferences.html.twig', array('form' => $form->createView(), 'salary' => $salary, 'choice_salary' => $finalChoice));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function docsCommunAction() {
        $postDatatable = $this->get("sg_datatables.codoc");
        $postDatatable->buildDatatable();
        return $this->render('FrontBundle:Codoc:index.html.twig', array('datatable' => $postDatatable));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function sdocWaitingAction() {
        $postDatatable = $this->get("sg_datatables.sdocwaiting");
        $postDatatable->buildDatatable();
        return $this->render('FrontBundle:Salary:sdoc_waiting.html.twig', array('datatable' => $postDatatable));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function sdocWaitingResultAction() {
        $datatable = $this->get("sg_datatables.sdocwaiting");
        $datatable->buildDatatable();





        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $function = function($qb) {
            // $qb->andWhere("post.title = :p");
            $qb->andWhere("document.specialDoc = :p");
            $qb->setParameter('p', true);
            $qb->andWhere("document.specialSigned = :p2");
            $qb->setParameter('p2', false);
        };

        $query->addWhereAll($function);



        return $query->getResponse();
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function coDocAddAction() {



        $doc = new CoDoc();
        $request = Request::createFromGlobals();
        $db = $this->getDoctrine()->getManager();
        $form = $this->createForm(new CoDocForm(), $doc, array(
            'cascade_validation' => true
        ));
        $salaryRep = $db->getRepository('FrontBundle:Salary');
        $catRep = $db->getRepository('FrontBundle:Category');
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

//                die('isValid');
                $file = $doc->getDoc();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                //move the file to the directory where documents are stored
                $docDir = '/docs_sign/' . $this->container->getParameter('client_id') . '/';
                $file->move($docDir, $fileName);
                // mouve filname to the directory($docDir)
                $doc->setDoc($fileName);
                $db->persist($doc);
                $db->flush();
//                die('flush');
                if ($doc->getToSign() == 1) {
//                    die('getToSign = 1');

                    $companies = $doc->getCompanies();
                    foreach ($companies as $company) {
                        $salaries = $salaryRep->findBy(array('company' => $company));
                        foreach ($salaries as $salary) {
                            $myDoc = new Document();
                            $myDoc->setDoc($fileName);
                            $myDoc->setSpecialDoc(true);
                            $myDoc->setVisibility(true);
                            $myDoc->setSalary($salary);
                            $myDoc->setSpecialSigned(false);
                            // create a new category for cummun signature.
                            $myCat = $catRep->findOneBy(array('code' => 'cosign'));
                            if (is_null($myCat)) {
                                $myCat = new Category();
                                $myCat->setCode('cosign');
                                $myCat->setLibelle('Signatures communes');
                                $db->persist($myCat);
                                $db->flush();
                            }
                            $myDoc->setCategory($myCat);
                            $myDoc->setName($doc->getName());
                            $db->persist($myDoc);
                            $db->flush();
                            if ($myDoc->getSpecialDoc()) {
                                $this->container->get('email_servies')->getHtmlDocSign($salary);
                            } else {
                                $this->container->get('email_servies')->getHtmlDoc($salary);
                            }
                        }
                    }
                }

                if ($doc->getToSign() == 0) {
//                    die('getToSign = 0');
                    $companies = $doc->getCompanies();
                    foreach ($companies as $key => $company) {
                        $salaries = $salaryRep->findBy(array('company' => $company));
                        foreach ($salaries as $key => $salary) {
                            $this->container->get('email_servies')->getHtmlDoc($salary);
                        }
                    }
                }


//                die('fin send mail');
                $this->get('session')->getFlashBag()->add('info', 'Le document est ajouté avec succès');
                return $this->redirect($this->generateUrl('front_docs_commun'));
            } else {
                //@TODO Message d'erreur à ajouter
            }
        }

        return $this->render('FrontBundle:Codoc:add.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function coDocRemoveAction($id) {
        $doc = $this->getDoctrine()
                ->getRepository('FrontBundle:CoDoc')
                ->getRepository('FrontBundle:CoDoc')
                ->find($id);

        if (!$doc) {
            throw $this->createNotFoundException(
                    'Aucun document trouvé pour cet id : ' . $id
            );
        }
        $em = $this->getDoctrine()->getManager();
        $cofile = '/docs_sign/' . $this->container->getParameter('client_id') . '/' . $doc->getDoc();
        $em->remove($doc);
        $em->flush();
        unlink($cofile);

        $this->get('session')->getFlashBag()->add('info', 'La document ' . $id . ' a été supprimé avec succès');
        return $this->redirect($this->generateUrl("front_docs_commun"));
    }

    /**
     * @Secure(roles="ROLE_RH, ROLE_RH_LIMITE")
     */
    public function coDocResultAction() {
        $datatable = $this->get("sg_datatables.codoc");
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }

    /**
     * @Secure(roles="ROLE_RH,ROLE_RH_LIMITE, ROLE_USER")
     */
    public function downloadCoDocAction($id) {
        $db = $this->getDoctrine()->getManager();
        $repDoc = $db->getRepository('FrontBundle:CoDoc');
        $repSalary = $db->getRepository('FrontBundle:Salary');
        $current_user = $this->get('security.context')->getToken()->getUser();
        $salary = $repSalary->findOneBy(array('user' => $current_user));
        // On va voir si ce salarié est authorisé à voir le document
        $doc = $repDoc->find($id);
        if (is_object($doc) && ($this->get('security.context')->isGranted('ROLE_USER') || $this->get('security.context')->isGranted('ROLE_RH')) && in_array($salary->getCompany()->getId(), $doc->getCompanies()->toArray())) {
            $data = file_get_contents('/docs_sign/' . $this->container->getParameter('client_id') . '/' . $doc->getDoc());
            $response = new Response($data, 200);
            $response->headers->set("Content-disposition", 'attachment; filename=doc_' . $doc->getDoc());
        } else {
            $this->get('session')->getFlashBag()->add('erreur', 'La document demandé n\'existe pas ');
            if ($this->get('security.context')->isGranted('ROLE_RH')) {
                $response = new RedirectResponse($this->get('router')->generate('front_homepage'));
            } else {
                $response = new RedirectResponse($this->get('router')->generate('front_my_profile'));
            }
        }
        return $response;
    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function signDocAction($id) {
        // be sure that the signer is the owner of the document
        $current_user = $this->get('security.context')->getToken()->getUser();
        $db = $this->getDoctrine()->getManager();
        $repDoc = $db->getRepository('FrontBundle:Document');
        $doc = $repDoc->find($id);
        $salary = $doc->getSalary();
        if ($doc->getSpecialDoc() && !$doc->getSpecialSigned() && $salary->getUser() == $current_user) {

            // prepare the signature

            $app_code = $this->container->getParameter('certeurope_certisms_app_code');
            $url = $this->container->getParameter('certeurope_certisms_url');
            $session = new Session();
            $telephone = $salary->getTelephonePerso();
            //Salary Informations
            $identifiant = $telephone;
            $indicatif = '33';
            // add access to sertisms
            try {
                $client = new \SoapClient($this->container->getParameter('certeurope_certisms_wsdl'), array('trace' => 1));
                $a = array('codeApplication' => $app_code, 'indicaddtifRegional' => $indicatif, 'identifiant' => $identifiant, 'url' => $url, 'paramaters' => null);
                $response = $client->__soapCall('AddAcces', $a);
                $filename = $doc->getDoc();
                // Générer un PDF
                // Display confirmation page
                $session->set('id', $id);
                return $this->render('FrontBundle:Salary:sign_confirmation.html.twig', array('salary' => $salary, 'sign_filename' => $filename, 'sign_telephone' => $telephone, 'indicatif' => $indicatif));
            } catch (\SoapFault $e) {
                throw new \Exception($client->__getLastResponse() . '---' . $e->getMessage());
            }
        } else {
            //message redirect the user to the profile page
            $this->get('session')->getFlashBag()->add('info', 'Impossible de signer ce document, veuillez contacter l\'administration');
            return $this->redirect($this->generateUrl("front_my_profile"));
        }
    }

    /**
     * @Secure(roles="ROLE_USER")
     */
    public function confirmSignAction() {

        $db = $this->getDoctrine()->getManager();
        $current_user = $this->get('security.context')->getToken()->getUser();
        $db = $this->getDoctrine()->getManager();
        $session = new Session();
        $request = Request::createFromGlobals();
        $app_code = $this->container->getParameter('certeurope_oneclick_app_code');
        $app_code_certi = $this->container->getParameter('certeurope_certisms_app_code');
        $indicatif = '33';
        $url = $this->container->getParameter('certeurope_oneclick_url');
        $session = new Session();
        $id = $session->get('id');
        $repDoc = $db->getRepository('FrontBundle:Document');
        $doc = $repDoc->find($id);
        $salary = $doc->getSalary();


        if ($request->getMethod() == 'POST') {
            // vérification des informations .
            $codeSMS = $request->get('sms_code');
            $sign_check = $request->get('sign_check');
            $sign_telephone = $request->get('sign_telephone');
            $sign_filename = $request->get('sign_filename');
            $indicatif = $request->get('indicatif');
            $identifiant = $sign_telephone;
            try {
                $client = new \SoapClient($this->container->getParameter('certeurope_certisms_wsdl'), array('trace' => 1));
                $a = array('codeApplication' => $app_code_certi, 'indicaddtifRegional' => $indicatif, 'identifiant' => $identifiant, 'code' => $codeSMS);
                $response = $client->__soapCall('CheckAcces', $a);
                $filename = '/docs_sign/' . $this->container->getParameter('client_id') . '/' . $sign_filename;
                $file_contents = file_get_contents($filename);
                $file_sha1 = sha1_file($filename);
                $signedPdf = $sign_filename;
                $email = $salary->getEmailPerso();
                if (is_null($email)) {
                    $email = $salary->getEmailPro();
                }
                if ($response->error == 0) {
                    $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
                    $xml .= "<REQUEST>";
                    $xml .= "<TYPE>1</TYPE>";
                    $xml .= "<APPLICATION_CODE>$app_code</APPLICATION_CODE>";
                    $xml .= "<REQUESTER_IDENTIFICATION>";
                    $xml .= "<NAME>{$salary->getNom()}</NAME>";
                    $xml .= "<FIRSTNAME>{$salary->getPrenom()}</FIRSTNAME>";
                    $xml .= "<EMAIL>{$email}</EMAIL>";
                    $xml .= "<ORGANISATION>  </ORGANISATION>";
                    $xml .= "</REQUESTER_IDENTIFICATION>";
                    $xml .= "<DOCUMENTS>";
                    $xml .= "<DOCUMENT>";
                    $xml .= "<TYPE>1</TYPE>";
                    $xml .= "<PARAM_SIGNATURE_PDF>";
                    $xml .= "<SIGNATURE_PDF_PAGE>1</SIGNATURE_PDF_PAGE>";
                    $xml .= "				<SIGNATURE_PDF_IMAGE>0</SIGNATURE_PDF_IMAGE>
				<SIGNATURE_PDF_LOGO_LLX>10</SIGNATURE_PDF_LOGO_LLX>
				<SIGNATURE_PDF_LOGO_LLY>10</SIGNATURE_PDF_LOGO_LLY>
				<SIGNATURE_PDF_LOGO_URX>240</SIGNATURE_PDF_LOGO_URX>
				<SIGNATURE_PDF_LOGO_URY>50</SIGNATURE_PDF_LOGO_URY>";
                    $xml .= "</PARAM_SIGNATURE_PDF>";
                    $xml .= "<SIGNATURE>0</SIGNATURE>";
                    $xml .= "<NAME>$sign_filename</NAME>";
                    $xml .= "<HASH>$file_sha1</HASH>";
                    $xml .= "<ALGO>SHA1</ALGO>";
                    $xml .= "<WITHDRAWAL></WITHDRAWAL>
                            <DEPOSIT></DEPOSIT>";
                    $xml .= "</DOCUMENT>";
                    $xml .= "</DOCUMENTS>";
                    $xml .= "</REQUEST>";

                    define('MULTIPART_BOUNDARY', '--------------------------' . microtime(true));
                    $header = 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDARY;
                    $content = "--" . MULTIPART_BOUNDARY . "\r\n" .
                            "Content-Disposition: form-data; name=\"file1\"; filename=\"" . $sign_filename . "\"\r\n" .
                            "Content-Type: application/pdf\r\n\r\n" .
                            $file_contents . "\r\n";

                    // add some POST fields to the request too: $_POST['foo'] = 'bar'
                    $content .= "--" . MULTIPART_BOUNDARY . "\r\n" .
                            "Content-Disposition: form-data; name=\"xmlReq\"\r\n\r\n" .
                            $xml . "\r\n";

                    // signal end of request (note the trailing "--")
                    $content .= "--" . MULTIPART_BOUNDARY . "--\r\n";


                    //Récupération et téléchargement du fichier
                    //Redirection vers la page resultat
                    $params = array('http' => array(
                            'method' => 'POST',
                            'header' => $header,
                            'content' => $content
                    ));

                    $ctx = stream_context_create($params);
                    $fp = @fopen($url, 'rb', false, $ctx);

                    if (!$fp) {
                        throw new Exception("Problem with $url, $php_errormsg");
                    }
                    $response = @stream_get_contents($fp);

                    if ($response === false) {
                        throw new Exception("Problem reading data from $url, $php_errormsg");
                        $this->get('session')->getFlashBag()->add('error', 'Veuillez SVP vérifier votre code SMS !');
                    } else {
                        $prefix = md5(uniqid());
                        $myfile = fopen('/docs_sign/' . $this->container->getParameter('client_id') . '/' . $prefix . '-' . $signedPdf, "w");
                        fwrite($myfile, $response);
                        fclose($myfile);
                        $doc->setDoc($prefix . '-' . $signedPdf);
                        // view Pdf
                        $doc->setSpecialSigned(true);
                        $db->flush();
                    }
                } else {
                    $this->get('session')->getFlashBag()->add('error', 'Veuillez SVP vérifier votre code SMS !');
                    try {
                        $client = new \SoapClient($this->container->getParameter('certeurope_certisms_wsdl'), array('trace' => 1));
                        $a = array('codeApplication' => $app_code_certi, 'indicaddtifRegional' => '33', 'identifiant' => $identifiant, 'url' => $url, 'paramaters' => null);
                        $response = $client->__soapCall('AddAcces', $a);
                    } catch (\SoapFault $e) {
                        throw new \Exception($client->__getLastResponse() . '---' . $e->getMessage());
                    }

                    //Rediriger l'utilisateur vers la page courante avec un message d'erreur.
                    return $this->render('FrontBundle:Salary:sign_confirmation.html.twig', array('salary' => $salary, 'sign_filename' => $sign_filename, 'sign_telephone' => $sign_telephone, 'indicatif' => $indicatif));
                }
            } catch (\SoapFault $e) {
                throw new \Exception($client->__getLastResponse() . '---' . $e->getMessage());
            }

            //Click one sign.
        }
        $this->get('session')->getFlashBag()->add('info', 'Document signé avec succès, il est maintenant disponible pour téléchargement sous l\'onglet "Documents"');
        return $this->redirect($this->generateUrl("front_my_profile"));
    }

}
