<?php

namespace BackBundle\Controller;

use BackBundle\Entity\Import;
use BackBundle\Entity\MassUpload;
use BackBundle\Entity\Personnalisation;
use BackBundle\Form\ImportForm;
use BackBundle\Form\MassUploadForm;
use BackBundle\Form\PersonnalisationForm;
use BackBundle\Helper\UploadHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BackBundle\Form\ZipForm;
use BackBundle\Entity\ZipFile;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @PreAuthorize("hasRole('ROLE_ADMIN') or hasRole('ROLE_RH')  or hasRole('ROLE_RH_LIMITE')")
 */
class DefaultController extends Controller {

    public function indexAction() {
        $postDatatable = $this->get("sg_datatables.zip");
        $postDatatable->buildDatatable();
        return $this->render('BackBundle:Default:index.html.twig', array('datatable' => $postDatatable));
    }

    public function zipResultsAction() {

        $datatable = $this->get("sg_datatables.zip");
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }

    public function manageMuAction() {
        $postDatatable = $this->get("sg_datatables.mu");
        $postDatatable->buildDatatable();
        return $this->render('BackBundle:Default:manage_mu.html.twig', array('datatable' => $postDatatable));
    }

    public function muResultsAction() {

        $datatable = $this->get("sg_datatables.mu");
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        $function = function($qb) {
            // $qb->andWhere("post.title = :p");
            $qb->andWhere("mass_upload.verified = :p");
            $qb->setParameter('p', 1);
        };

        $query->addWhereAll($function);

        return $query->getResponse();
    }

    public function disableZipAction($id) {
        $db = $this->getDoctrine()->getManager();
        $repZipFile = $db->getRepository('BackBundle:ZipFile');
        $zip = $repZipFile->find($id);
        if (is_object($zip)) {
            $clientId = $this->container->getParameter('client_id');
            $zipLocation = $this->container->getParameter('zip_location');
            $zipLocation = $zipLocation . '/' . $clientId . '/';
            $file_zip = $zipLocation . $zip->getName() . '.zip';
            @unlink($file_zip);
            $uid = md5(uniqid());
            $zip->setName($zip->getName() . '_' . $uid);
            $zip->setDisabled(true);

            $db->flush();
            $this->get('session')->getFlashBag()->add('info', ' Le zip selectionné a été désactivé avec succès');
            return $this->redirect($this->generateUrl('back_homepage'));
        }

        return new Response('Bad Request', 400);
    }

    public function displayLogsAction($id) {

        $db = $this->getDoctrine()->getManager();
        $logs = $db->getRepository('BackBundle:LogZip')->findBy(array('zip' => $id));

        return $this->render('BackBundle:Default:display_logs.html.twig', array('logs' => $logs, 'id' => $id));
    }

    public function displayLogsMuAction($id) {

        $db = $this->getDoctrine()->getManager();
        $logs = $db->getRepository('BackBundle:LogMu')->findBy(array('mu' => $id));

        return $this->render('BackBundle:Default:display_logs_mu.html.twig', array('logs' => $logs, 'id' => $id));
    }

    public function disableBuAction($id) {
        $db = $this->getDoctrine()->getManager();
        $rep = $db->getRepository('BackBundle:Bu');
        $bu = $rep->find($id);
        if (is_object($bu)) {
            $buLocation = '/docs_sign/' . $this->container->getParameter('client_id') . '/unite/';

//            $buLocation = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/docs_sign/' . $this->container->getParameter('client_id') . '/unite/';
            @unlink($buLocation . $bu->getBulletin());

            $bu->setDisabled(true);

            $db->flush();
            $this->get('session')->getFlashBag()->add('info', ' Le bulletin selectionné a été désactivé avec succès');
            return $this->redirect($this->generateUrl('bu_list'));
        }

        return new Response('Bad Request', 400);
    }

    public function uploadAction(Request $request) {

        $class = new ZipFile();
        $db = $this->getDoctrine()->getManager();
        $form = $this->createForm(new ZipForm(), $class);
        $prefixFile = $this->container->getParameter('prefix_zip');

        if ($request->getMethod() == 'POST') {

            $form->bind($request);
            if ($form->isValid()) {

                $prefixZip = $this->container->getParameter('prefix_zip');
                $sftpFile = $this->container->getParameter('ftps_file');
                $strServer = $this->container->getParameter('ftps_host');
                $strServerPort = $this->container->getParameter('ftps_port');
                $strServerUsername = $this->container->getParameter('ftps_login');
                $strServerPassword = $this->container->getParameter('ftps_pwd');
                $clientId = $this->container->getParameter('client_id');
                $zipLocation = $this->container->getParameter('zip_location');
                $zipLocation = $zipLocation . '/' . $clientId . '/';
                if (!file_exists($zipLocation)) {
                    mkdir($zipLocation, 0777, true);
                }
                $conn_id = ftp_ssl_connect($strServer, $strServerPort);

                // Identification avec un nom d'utilisateur et un mot de passe
                $login_result = ftp_login($conn_id, $strServerUsername, $strServerPassword);

                ftp_pasv($conn_id, true);
                if (($conn_id) && ($login_result)) {

                    // Tentative de téléchargement du fichier $server_file et sauvegarde dans le fichier $local_file
                    if (@ftp_get($conn_id, $zipLocation . $class->getName() . '.zip', $sftpFile . $class->getName() . '.zip', FTP_BINARY)) {
                        $extFile = explode('_', $class->getName());
                        $prefix = $extFile[0];
                        if ($prefix == $prefixZip) {
                            $db->persist($class);
                            $db->flush();
                            $this->get('session')->getFlashBag()->add('info', ' Le fichier demandé est maintenant planifié pour la signature électronique');
                            return $this->redirect($this->generateUrl('back_homepage'));
                        } else {
                            $this->get('session')->getFlashBag()->add('erreur', 'Le fichier demandé est mal nommé');
                        }
                    } else {
                        $this->get('session')->getFlashBag()->add('erreur', 'Le fichier demandé n\'existe pas dans le serveur FTP');
                    }

                    // Fermeture de la connexion
                    ftp_close($conn_id);
                } else {
                    $this->get('session')->getFlashBag()->add('erreur', 'Impossible de se connecter à FTPs');
                }
            }
        }


        return $this->render('BackBundle:Default:upload.html.twig', array('form' => $form->createView(), 'prefix' => $prefixFile));
    }

    public function uploadMassAction(Request $request) {

        $db = $this->getDoctrine()->getManager();

//         var_dump($request->getMethod());
//            die('ici');
        if ($request->getMethod() != 'POST') {
            // Clear
            $uploadUid = md5(uniqid());
            $mu = new MassUpload();
            $mu->setMonth(date('m'));
            $mu->setYear(date('Y'));
            $mu->setVerified(false);
            $mu->setUidUpload($uploadUid);
            $db->persist($mu);
            $db->flush();
        } else {
//            var_dump($request->getMethod());
//            die('ici post');
            $uid = $request->get('uid');
            $mu = $db->getRepository('BackBundle:MassUpload')->findOneBy(array('uidUpload' => $uid));
//             var_dump($mu);
//            die('$mu');
        }
        $form = $this->createForm(new MassUploadForm(), $mu);
//        var_dump($request->getMethod());
//            die('ici ???');
        if ($request->getMethod() == 'POST') {
            
            $form->handleRequest($request);
            $nbBulletins = $request->get('nb_bulletins');
//             var_dump($nbBulletins);
//            die('$nbBulletins');
            $buErrone = $request->get('bu_e');
//             var_dump($buErrone);
//            die('$buErrone');

            if ($form->isValid()) {
                if ($nbBulletins > 0 && $buErrone == 0) {
                    $mu->setNbBulletins($nbBulletins);
                    $db->persist($mu);    
                    $db->flush();
//                     die('ici');
                    $this->get('session')->getFlashBag()->add('info', ' Les bulletins de paies ont été planifiés pour la signature électronique');
                    return $this->redirect($this->generateUrl('manage_mu'));
                } else {
                    if ($nbBulletins == 0) {
                        $this->get('session')->getFlashBag()->add('erreur', 'Il faut uploader au moins un seul bulletins');
                    }
                    if ($buErrone > 0) {
                        $this->get('session')->getFlashBag()->add('erreur', 'Des bulletins erronés ont été trouvés, veuillez revérifier votre dépôt');
                    }
                    return $this->redirect($this->generateUrl('back_upload_mass'));
                }
            }
        }
         
//            die('fin');
        return $this->render('BackBundle:Default:mass_upload.html.twig', array('form' => $form->createView(), 'uidupload' => $uploadUid));
    }

    public function getSalaryAction(Request $request) {
        $db = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'GET') {
            $matricule = $request->get('matricule');
            $company = $request->get('company');

            $fullEx = explode('_', $matricule);
            if (count($fullEx) > 1) {
                $matricule = $fullEx[0];
            }
            $repSalary = $db->getRepository('FrontBundle:Salary');
            $repCompany = $db->getRepository('FrontBundle:Company');


            if (empty($company)) {
                $salaries = $repSalary->findBy(array('matricule' => $matricule));
            } else {
                $myCompany = $repCompany->find($company);
                $salaries = $repSalary->findBy(array('matricule' => $matricule, 'company' => $myCompany));
            }

            if (count($salaries) == 1) {
                $salary = $salaries[0];
                return new Response($salary->getPrenom() . ' ' . $salary->getNom(), 200);
            } elseif (count($salaries) == 0) {
                return new Response('1', 200);
            } else {
                return new Response('2', 200);
            }
        }
        return new Response('', 200);
    }

    public function displayVerifAction(Request $request) {
        $db = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'GET') {
            $matricule = $request->get('matricule');
            $company = $request->get('company');
            $uid = $request->get('uid');
            $matricule = trim($matricule);
            $fileName = $matricule;
            $fullEx = explode('_', $matricule);
            if (count($fullEx) > 1) {
                $matricule = $fullEx[0];
            }

            $repSalary = $db->getRepository('FrontBundle:Salary');
            $repCompany = $db->getRepository('FrontBundle:Company');

            if (empty($company)) {
                $salary = $repSalary->findOneBy(array('matricule' => $matricule));
            } else {
                $myCompany = $repCompany->find($company);
                $salary = $repSalary->findOneBy(array('matricule' => $matricule, 'company' => $myCompany));
            }
        }

        return $this->render('BackBundle:Default:ajax/verify_bulletin.html.twig', array('matricule' => $matricule, 'uid' => $uid, 'salary' => $salary, 'filename' => $fileName));
    }

    public function getBulletinAction($uid, $token) {
        $ext = 'pdf';
        $clientId = $this->container->getParameter('client_id');
        $zipLocation = $this->container->getParameter('zip_location');
        $pdf = $zipLocation . '/files/' . $clientId . '/' . $uid . '/' . trim($token);
        $fullpath = null;
        if (file_exists($pdf . '.' . strtoupper($ext))) {
            $fullpath = $pdf . '.' . strtoupper($ext);
        } else if (file_exists($pdf . '.' . strtolower($ext))) {
            $fullpath = $pdf . '.' . strtolower($ext);
        }
        $response = new Response(file_get_contents($fullpath), 200);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }

    public function uploadFilesAction($uid) {
        $clientId = $this->container->getParameter('client_id');
        $zipLocation = $this->container->getParameter('zip_location');
        $options = array(
            'upload_dir' => $zipLocation . '/files/' . $clientId . '/' . $uid . '/',
            'upload_url' => $zipLocation . '/files/' . $clientId . '/' . $uid . '/',
        );
        $upload_handler = new UploadHandler($options);
        return new Response('', 200);
    }

    public function importSalariesAction(Request $request) {
        $db = $this->getDoctrine()->getManager();
        $class = new Import();
        $form = $this->createForm(new ImportForm(), $class);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $columns = $request->get('columns');
            if ($form->isValid()) {
                if (!is_null($class->getImportFile())) {
                    $file = $class->getImportFile();
                    $fileName = md5(uniqid()) . '.' . 'csv';
//                    $fileName= 'myfile'. '.' . 'csv';
                       $dir = '/docs_sign/' . $this->container->getParameter('client_id') . '/import/';
//                    $dir = $dir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/docs_sign/' . $this->container->getParameter('client_id') . '/import/';
//                 $dir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/';
//                    var_dump($fileName);
//                    die('$fileName');
                    $file->move($dir, $fileName);

                    $class->setImportFile($fileName);
                }
                $class->setColumns($columns);
                $db->persist($class);
                $db->flush();
                $this->get('session')->getFlashBag()->add('info', ' Import planifié avec succès');
//                die('fin');
                return $this->redirect($this->generateUrl('import_salaries_list'));
            }
            // Traitement de l'upload
        }

        // display Form
        return $this->render('BackBundle:Default:import_salaries_add.html.twig', array('form' => $form->createView()));
    }

    public function importSalariesListAction() {
        $postDatatable = $this->get("sg_datatables.import");
        $postDatatable->buildDatatable();
        return $this->render('BackBundle:Default:import_salaries.html.twig', array('datatable' => $postDatatable));
    }

    public function importSalariesResultAction() {

        $datatable = $this->get("sg_datatables.import");
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }

    public function personnalisationAction(Request $request) {

        $db = $this->getDoctrine()->getManager();

        $class = $db->getRepository('BackBundle:Personnalisation')->find(1);
        if (!is_object($class)) {
            $class = new Personnalisation();
        }
        $form = $this->createForm(new PersonnalisationForm(), $class);
        $oldLogo = $class->getLogo();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if (!is_null($class->getLogo())) {
                    $file = $class->getLogo();
                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                    //move the file to the directory where documents are stored
                    $dir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/';
                    $file->move($dir, $fileName);
                    $class->setLogo($fileName);
                } else {
                    $class->setLogo($oldLogo);
                }
                $this->get('session')->getFlashBag()->add('info', ' La personnalisation a été mise à jour avec succès');
                $db->persist($class);
                $db->flush();
            }
        }
        return $this->render('BackBundle:Default:personnalisation.html.twig', array('form' => $form->createView(), 'perso' => $class));
    }

    public function printDepotsAction() {

        $db = $this->getDoctrine()->getManager();
        $printDepots = $db->getRepository('BackBundle:PrintDepots')->findBy(
                array(), array('id' => 'DESC')
        );
        return $this->render('BackBundle:Default:print_depots.html.twig', array('depots' => $printDepots));
    }

}
