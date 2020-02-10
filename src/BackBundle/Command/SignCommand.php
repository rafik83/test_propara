<?php

/*
 * This file is part of the fulldon project
 *
 * (c) SAMI BOUSSACSOU <boussacsou@intersa.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BackBundle\Command;

use BackBundle\Entity\LogMu;
use BackBundle\Entity\LogZip;
use BackBundle\Entity\PrintDepots;
use BackBundle\Entity\Stat;
use BackBundle\Entity\ZipFile;
use FrontBundle\Entity\Company;
use FrontBundle\Entity\Role;
use FrontBundle\Entity\Salary;
use BackBundle\Entity\Bu;
use FrontBundle\Entity\User;
use FrontBundle\Entity\ActivateSalary;
use FrontBundle\Entity\SignedDoc;
use BackBundle\Entity\MassUpload;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Finder\SplFileInfo;

//use Symfony\Component\DependencyInjection\ContainerInterface as Container;


class SignCommand extends ContainerAwareCommand {

    //private $container;
    //public function __construct(Container $container) {
    //$this->container = $container;
    //}

    protected function configure() {
        $this
                ->setName('sign:zip')
                ->setDescription('Signature du contenu de Zip')
                ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $salariesListForPaper = array();
        $salariesPrint2p = array();
        $salariesPrint1p = array();
        // Garbage collector
        gc_collect_cycles();
        $app = new Application();
        $progress = $app->getHelperSet()->get('progress');
        $zipLocation = $this->getContainer()->getParameter('zip_location');
        $clientId = $this->getContainer()->getParameter('client_id');
        //$progress->start($output, count($dons));
        $certSign = $this->getContainer()->get('cert_sign.server');
        //$progress->start($output, $nb);

        $db = $this->getContainer()->get('doctrine')->getManager();
        $repZip = $db->getRepository('BackBundle:ZipFile');
        $repSalary = $db->getRepository('FrontBundle:Salary');
        $repDocSign = $db->getRepository('FrontBundle:SignedDoc');
        $rep = $db->getRepository('BackBundle:Bu');
        $repMu = $db->getRepository('BackBundle:MassUpload');
        $repImport = $db->getRepository('BackBundle:Import');
        $repCompany = $db->getRepository('FrontBundle:Company');
        $repUser = $db->getRepository('FrontBundle:User');
//        $printDir = $this->getContainer()->getParameter('kernel.root_dir') . '/../web/uploads/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint/';


    
        $printDir = '/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint/';
        // Remove all the depots that are not verified
        $currentDate = new \DateTime();
        $currentDate = $currentDate->sub(new \DateInterval('PT1H'));
        $depotsToRemove = $repMu
                ->createQueryBuilder('m')
                ->where('m.verified = false')
                ->andwhere('m.createdAt <= :date')
                ->setParameter('date', $currentDate->format('Y-m-d H:i:s'))
                ->getQuery()
                ->getResult();
//        echo 'depotsToRemove';
//         echo var_dump($depotsToRemove);
//        echo 'ziplocation';
        // echo var_dump($zipLocation);
//        foreach ($depotsToRemove as $dtr) {
//            $toRemove = $zipLocation . '/files/' . $clientId . '/' . $dtr->getUidUpload();
//            //Remove from hard disk
//            //echo var_dump($toRemove);
//            $this->deleteDirectory($toRemove);
//            $db->remove($dtr);
//            $db->flush();
//        }
        /** ******************** Gestion des imports ******************************** */

        $imports = $repImport->findBy(array('progress' => 0));
        $progress->start($output, count($imports));
        $output->writeln("Début du traitement des imports \n");
        //$progress->start($output, iterator_count($fi));



        try {
            foreach ($imports as $import) {
                // En cours
                $import->setProgress(1);
                $db->flush();

                $columns = $import->getColumns();
                $coldisplay = array();
                $mycol = explode('&', $columns);
                foreach ($mycol as $col) {
                    $coldisplay[] = substr($col, 7, strlen($col));
                }

                $role_salary = 3;

//                $dir = $this->getContainer()->getParameter('kernel.root_dir') . '/../web/uploads/' . 'myfile.csv';
//                $dir = $this->getContainer()->getParameter('kernel.root_dir') . '/../web/uploads/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/import/' . $import->getImportFile();
                //$output->writeln($dir);
                $dir = '/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/import/' . $import->getImportFile(); //myfile.csv
                $filePath = $dir;
                $formaD = null;
                switch ($import->getFormatDate()) {
                    case 'jma':
                        $formaD = 'd/m/Y';
                        break;
                    case 'mja':
                        $formaD = 'm/d/Y';
                        break;
                    case 'amj':
                        $formaD = 'Y-m-d';
                        break;
                }
                if (($handle = fopen($filePath, "r")) !== FALSE) { //!== FALSE
                    $i = 0;
                    $premiere_ligne = 0;
                    $output->writeln("enter open file");
                    $tab_search = array();
                    $tab_user_id = array();
                    $index_search = 0;
                    $trouve_num_secu = 0;
                    $existe_ss = 0;
                    $tab_struct_file = array();
                    $index_structe_file = 0;
                    $columns_egaux = 0;
                    $tab_search_matricule = array();
                    $index_search_matricule = 0;
                    $trouve_matricule = 0;
                    $trouve_company = 0;
                    $cless = 0;
                    $array_doublon_ids = array();
                    $index_doublon_ids = 0;

//                    if (mb_check_encoding(file_get_contents($dir), 'UTF-8')) {
                    // yup, all UTF-8
//                        $output->writeln("yup, all UTF-8");
                    while (($data = fgetcsv($handle, 0, $import->getSeparateur())) !== FALSE) {
//                        if(count($data)> 1){
                        // teste data
                        if (count($data) == count($coldisplay)) {
                            $cless = 0;
                            if (array_search('cless', $coldisplay) !== false) {
                                $cless = addslashes(trim($data[array_search('cless', $coldisplay)]));
                                if (!empty($cless)) {
                                    $output->writeln("cless not empty");
                                    $output->writeln($cless);
                                } else {
                                    if ($cless == "") {
                                        $import->setCommentError('Problème dans la structure du fichier, Clé de sécurité sociale  est Vide');
                                        $output->writeln("Clé de sécurité sociale  est Vide");
                                        $import->setProgress(99);
                                        $db->flush();
                                        die();
//                                        
                                    }
                                }
                            } else {
                                $import->setCommentError('Problème dans la structure du fichier, Clé de sécurité sociale  n\'éxiste pas');
                                $output->writeln("Clé de sécurité sociale  n\'éxiste pas");
                                $import->setProgress(99);
                                $db->flush();
                                die();
                            }
                            $output->writeln("cless is");
                            $output->writeln(addslashes(trim($data[array_search('cless', $coldisplay)])));
                            // debut teste matricule is numéric
                            if (is_numeric(addslashes(trim($data[array_search('cless', $coldisplay)])))) {
                                $output->writeln(is_int(addslashes(trim($data[array_search('cless', $coldisplay)]))));
                                $output->writeln("entreprise");
                                $data_entreprise = $data[array_search('entreprise', $coldisplay)]; //addslashes(trim($data[array_search('entreprise', $coldisplay)]));
                                $entreprise = utf8_encode($data_entreprise);
                                $Companyexiste = $db->getRepository('FrontBundle:Company')->byCompany($entreprise);
                                if (!$Companyexiste) {

                                    $import->setCommentError('Problème dans la structure du fichier, nom de societé' . ':' . ' ' . $entreprise . ' ' . 'invalide' . ' ' . 'pour le salarié' . ' ' . $prenom = addslashes(trim($data[array_search('prenom', $coldisplay)])) . ' ' . $nom = addslashes(trim($data[array_search('nom', $coldisplay)])) . '');
                                    $output->writeln("nom entreprise invalide");
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }
                                // teste if nom existe
                                if (array_search('nom', $coldisplay) !== false) {
                                    $nom = addslashes(trim($data[array_search('nom', $coldisplay)]));
                                    $nom = utf8_encode($nom);
                                    if (!empty($nom)) {
                                        $output->writeln("nom not empty");
                                        $output->writeln($nom);
                                    } else {
                                        if ($nom == "") {
                                            $output->writeln("nom  empty");
                                            $output->writeln($nom);
                                            $import->setCommentError('Nom  obligatoire pour le salarié qui a le N° sécurité sociale' . ' ' . addslashes(trim($data[array_search('ss', $coldisplay)])));
                                            $import->setProgress(99);
                                            $db->flush();
                                            die();
                                        }
                                    }
                                } else {
                                    $output->writeln("nom n'existe pas dans array_search data");
                                    $import->setCommentError('Nom  obligatoire pour le salarié qui a le N° sécurité sociale' . ' ' . addslashes(trim($data[array_search('ss', $coldisplay)])));
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }

                                // teste if prenom existe
                                if (array_search('prenom', $coldisplay) !== false) {
                                    $prenom = addslashes(trim($data[array_search('prenom', $coldisplay)]));
                                    $prenom = utf8_encode($prenom);
                                    if (!empty($prenom)) {
                                        $output->writeln("prenom not empty");
                                        $output->writeln($prenom);
                                    } else {
                                        if ($prenom == "") {
                                            $output->writeln("prenom  empty");
                                            $output->writeln($prenom);
                                            $import->setCommentError('Prénom  obligatoire pour le salarié qui a le N° sécurité sociale' . ' ' . addslashes(trim($data[array_search('ss', $coldisplay)])));
                                            $import->setProgress(99);
                                            $db->flush();
                                            die();
                                        }
                                    }
                                } else {
                                    $output->writeln("prenom n'existe pas dans array_search data");
                                    $import->setCommentError('Prénom  obligatoire pour le salarié qui a le N° sécurité sociale' . ' ' . addslashes(trim($data[array_search('ss', $coldisplay)])));
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }


                                // teste if ss existe
                                if (array_search('ss', $coldisplay) !== false) {
                                    $numSecu = addslashes(trim($data[array_search('ss', $coldisplay)]));
                                    if (!empty($numSecu)) {
                                        $output->writeln("ss not empty");
                                        $output->writeln($numSecu);
                                    } else {
                                        if ($numSecu == "") {
                                            $output->writeln("ss  empty");
                                            $output->writeln($numSecu);
                                            $import->setCommentError('N°sécurité sociale est obligatoire pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                            $import->setProgress(99);
                                            $db->flush();
                                            die();
                                        }
                                    }
                                } else {
                                    $output->writeln("ss n'existe pas dans array_search data");
                                    $import->setCommentError('N°sécurité sociale est obligatoire pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }

                                // teste if emploi empty
                                if (array_search('emploi', $coldisplay) !== false) {
                                    $emploi = addslashes(trim($data[array_search('emploi', $coldisplay)]));
                                    if (!empty($emploi)) {
                                        $output->writeln("emploi not empty");
                                        $output->writeln($emploi);
                                    } else {
                                        if ($emploi == "") {
                                            $output->writeln("emploi  empty");
                                            $output->writeln($emploi);
                                        }
                                    }
                                } else {
                                    $output->writeln("emploi n'existe pas dans array_search data");
                                    $emploi = "";
                                }

                                // teste if naturecontrat empty
                                if (array_search('naturecontrat', $coldisplay) !== false) {
                                    $contrat = addslashes(trim($data[array_search('naturecontrat', $coldisplay)]));
                                    if (!empty($contrat)) {
                                        $output->writeln("contrat not empty");
                                        $output->writeln($contrat);
                                    } else {
                                        if ($contrat == "") {
                                            $output->writeln("contrat  empty");
                                            $output->writeln($contrat);
                                        }
                                    }
                                } else {
                                    $output->writeln("naturecontrat n'existe pas dans array_search data");
                                    $contrat = "";
                                }

                                // teste if adresse empty
                                if (array_search('adresse', $coldisplay) !== false) {
                                    $adresse = addslashes(trim($data[array_search('adresse', $coldisplay)]));
                                    if (!empty($adresse)) {
                                        $output->writeln("adresse not empty");
                                        $output->writeln($adresse);
                                    } else {
                                        if ($adresse == "") {
                                            $output->writeln("adresse  empty");
                                            $output->writeln($adresse);
                                        }
                                    }
                                } else {
                                    $output->writeln("adresse n'existe pas dans array_search data");
                                    $adresse = "";
                                }

                                // teste if cpmt empty
                                if (array_search('cmpadresse', $coldisplay) !== false) {
                                    $cmpadresse = addslashes(trim($data[array_search('cmpadresse', $coldisplay)]));
                                    if (!empty($cmpadresse)) {
                                        $adresse = $adresse . ' ' . $cmpadresse;
                                    } else {
                                        if ($cmpadresse == "") {
                                            $adresse = $adresse;
                                        }
                                    }
                                } else {
                                    $output->writeln("cmpadresse n'existe pas dans array_search data");
                                    $adresse = "";
                                }





                                // teste if ville empty
                                if (array_search('ville', $coldisplay) !== false) {
                                    $ville = addslashes(trim($data[array_search('ville', $coldisplay)]));
                                    if (!empty($ville)) {
                                        $output->writeln("ville not empty");
                                        $output->writeln($ville);
                                    } else {
                                        if ($ville == "") {
                                            $output->writeln("ville  empty");
                                            $output->writeln($ville);
                                        }
                                    }
                                } else {
                                    $output->writeln("ville n'existe pas dans array_search data");
                                    $ville = "";
                                }

                                // teste if zipcode empty

                                if (array_search('zipcode', $coldisplay) !== false) {
                                    $zipcode = addslashes(trim($data[array_search('zipcode', $coldisplay)]));
                                    if (!empty($zipcode)) {
                                        $output->writeln("zipcode not empty");
                                        $output->writeln($zipcode);
                                    } else {
                                        if ($zipcode == "") {
                                            $output->writeln("zipcode  empty");
                                            $output->writeln($zipcode);
                                        }
                                    }
                                } else {
                                    $output->writeln("zipcode n'existe pas dans array_search data");
                                    $zipcode = "";
                                }


                                /**                                 * ****************** Gestion des dates ******************** */
                                if (array_search('dateentree', $coldisplay) !== false) {
                                    if (!empty(trim($data[array_search('dateentree', $coldisplay)]))) {
                                        $date_debut = \DateTime::createFromFormat($formaD, trim($data[array_search('dateentree', $coldisplay)]));
                                    } else {
                                        $date_debut = null;
                                        $output->writeln($date_debut);
                                        $import->setCommentError('La Date de début est obligatoire pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                        $import->setProgress(99);
                                        $db->flush();
                                        die();
                                    }
                                } else {
                                    $date_debut = null;
                                    $output->writeln($date_debut);
                                    $import->setCommentError('La Date de début est obligatoire pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }

                                if (array_search('datesortie', $coldisplay) !== false) {
                                    if (!empty(trim($data[array_search('datesortie', $coldisplay)]))) {


                                        $date_fin = \DateTime::createFromFormat($formaD, trim($data[array_search('datesortie', $coldisplay)]));
                                        $output->writeln("date sortie not empty");
                                    } else {
                                        $date_fin = null;
                                        $output->writeln("date sortie  empty");
                                    }
                                } else {
                                    $output->writeln("date de sortie n'existe pas dans array_search data");
                                    $date_fin = null;
                                }
                                // teste date naissance
                                if (array_search('datenaissance', $coldisplay) !== false) {
                                    if (!empty(trim($data[array_search('datenaissance', $coldisplay)]))) {

                                        $date_naissance = \DateTime::createFromFormat($formaD, trim($data[array_search('datenaissance', $coldisplay)]));
                                    } else {
                                        $date_naissance = null;
                                        $output->writeln($date_naissance);
                                        $import->setCommentError('Date de naissance est obligatoire pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                        $import->setProgress(99);
                                        $db->flush();
                                        die();
                                    }
                                } else {
                                    $date_naissance = null;
                                    $output->writeln($date_naissance);
                                    $import->setCommentError('Date de naissance est obligatoire pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }
                                /**                                 * **************** Fin de la gestion des dates ***************** */
                                // teste if matricule existe
                                if (array_search('matricule', $coldisplay) !== false) {
                                    $matricule = addslashes(trim($data[array_search('matricule', $coldisplay)]));
                                    if (!empty($matricule)) {
                                        $output->writeln("matricule not empty");
                                        $output->writeln($matricule);
                                    } else {
                                        if ($matricule == "") {
                                            $output->writeln("matricule  empty");
                                            $output->writeln($matricule);
                                            $import->setCommentError('Matricule  obligatoire pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                            $import->setProgress(99);
                                            $db->flush();
                                            die();
                                        }
                                    }
                                } else {
                                    $output->writeln("Le matricule n'existe pas dans array_search data");
                                    $matricule = "";
                                    $import->setCommentError('le Matricule  n\'éxiste pas dans le fichier' . 'Attention !!');
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }



                                // teste email perso if existe
                                if (array_search('emailperso', $coldisplay) !== false) {
                                    $emailperso = addslashes(trim($data[array_search('emailperso', $coldisplay)]));
                                    if (!empty($emailperso)) {
//                                        $output->writeln("emailperso not empty");
//                                        $output->writeln($emailperso);
                                    } else {
                                        if ($emailperso == "") {
//                                            $output->writeln("emailperso  empty");
//                                            $output->writeln($emailperso);
                                            $import->setCommentError('E-mail Personnelle est obligatoire pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                            $import->setProgress(99);
                                            $db->flush();
                                            die();
                                        }
                                    }
                                } else {
                                    $output->writeln("emailperso n'existe pas dans array_search data");
                                    $emailperso = "";
                                    $import->setCommentError('E-mail Personnelle n \'éxiste dans le fichier  pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }





                                // teste clef ss if existe
                                if (array_search('cless', $coldisplay) !== false) {
                                    $ClefSecu = addslashes(trim($data[array_search('cless', $coldisplay)]));
                                    if (!empty($ClefSecu)) {
                                        $output->writeln("cless not empty");
                                        $output->writeln($ClefSecu);
                                    } else {
                                        if ($ClefSecu == "") {
                                            $output->writeln("cless  empty");
                                            $output->writeln($ClefSecu);
                                            $import->setCommentError('Clé de sécurité sociale est obligatoire pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                            $import->setProgress(99);
                                            $db->flush();
                                            die();
                                        }
                                    }
                                } else {
                                    $output->writeln("cless n'existe pas dans array_search data");
                                    $ClefSecu = "";
                                    $import->setCommentError('Clé de sécurité sociale n éxiste pas dans le fichier pour le salarié' . ' ' . addslashes(trim($data[array_search('nom', $coldisplay)])) . ' ' . utf8_encode(addslashes(trim($data[array_search('prenom', $coldisplay)]))));
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }


                                if (array_search('telephoneperso', $coldisplay) !== false) {
                                    $telephone = trim($data[array_search('telephoneperso', $coldisplay)]);
                                } else {
                                    $telephone = "0000000000";
                                }

                                // search all matricule in this file
                                if (array_search('matricule', $coldisplay) !== false) {
                                    $tab_search_matricule[$index_search_matricule] = trim($data[array_search('matricule', $coldisplay)]);
                                    $index_search_matricule ++;
                                }
                                // search all ss in this file
                                if (array_search('ss', $coldisplay) !== false) {
                                    $tab_search[$index_search] = trim($data[array_search('ss', $coldisplay)]);
                                    $index_search ++;
                                }



                                // teste salary en demat ou papier
                                $bupapier = -1;

                                if ($emailperso == 'fulloffice@3cexperts.fr') {
                                    $bupapier = 1;
                                } else {
                                    $bupapier = 0;
                                }
                                if ($bupapier == -1) {
                                    $import->setCommentError('Problème dans la structure du fichier, E-mail personelle est obligatoire pour le salarier' . ' ' . ':' . $prenom . ' ' . $nom); //$prenom
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }


                                $company_id = NULL;
                                $user_id = NULL;
                                $num_secu_cless = addslashes(trim($data[array_search('ss', $coldisplay)])) . '' . addslashes(trim($data[array_search('cless', $coldisplay)]));
//                                $output->writeln("fiiiii999999999 houniiiiiiiiiiiiiii");
                                $etabli = utf8_encode(trim($data[array_search('entreprise', $coldisplay)]));
//                                    $output->writeln($etabli);
                                $get_Company = $db->getRepository('FrontBundle:Company')->findOneBy(array('nom' => $etabli));
                                $get_User = $repUser->findOneBy(array('username' => $numSecu));
//                                $output->writeln($get_User->getId());
//                                die('houni fi9999999999999999999999999999999');
                                if ($get_Company) {
                                    $company_id = $get_Company->getId();
                                }

                                if ($get_User) {
                                    $user_id = $get_User->getId();
                                }



//                                if ($company_id != NULL && $user_id != NULL) {
//
//                                    $array_doublon_ids[$index_doublon_ids]['company_id'] = $company_id;
//                                    $array_doublon_ids[$index_doublon_ids]['user_id'] = $user_id;
//                                }
//                                if (count($array_doublon_ids) > 1) {
//                                    for ($i = 0; $i < count($array_doublon_ids); $i++) {
//                                        $output->writeln("iteration" . $i);
//                                        $output->writeln(var_dump($array_doublon_ids[$i]['company_id']));
//                                        $output->writeln(var_dump($array_doublon_ids[$i]['user_id']));
//                                        for ($j = $i + 1; $j < count($array_doublon_ids); $j++) {
//                                            if (($array_doublon_ids[$i]['company_id'] == $array_doublon_ids[$j]['company_id']) && ($array_doublon_ids[$i]['user_id'] == $array_doublon_ids[$j]['user_id'])) {
//                                                $import->setCommentError(' Doublon détecter pour le Salarier :' . ' ' . $prenom . ' ' . $nom);
//                                                $import->setProgress(99);
//                                                $db->flush();
//                                                die();
//                                            }
//                                        }
//                                    }
//                                }
//$matricule
                                if ($company_id != NULL) {
                                    if ($user_id != NULL) {
                                        if ($matricule != NULL) {
                                            $array_fetch_salary_by = $this->fetchExisteSalaryBy($company_id, $user_id, $matricule);
                                            if (count($array_fetch_salary_by) > 0) {
                                                // update salary
                                                $id_salary = $array_fetch_salary_by[0]['id'];
                                                $user_id = $array_fetch_salary_by[0]['user_id'];
                                                $company_id = $array_fetch_salary_by[0]['company_id'];
                                                if ($id_salary != "") {
                                                    if ($id_salary != NULL) {
                                                        $Salary_Update = $repSalary->find($id_salary);
                                                        //$User_in_salary = $repUser->find($user_id);
                                                        $NewCompany = $db->getRepository('FrontBundle:Company')->findOneBy(array('nom' => $etabli));
                                                        if ($NewCompany) {
                                                            if ($Salary_Update) {
                                                                //update Salary
                                                                // set company NULL dans le cas ou le Salary veut changer de entity
                                                                $output->writeln("update");
                                                                $Salary_Update->setCompany(NULL);
                                                                $db->persist($Salary_Update);
                                                                $db->flush();
                                                                $Salary_Update->setNom($nom);
                                                                $Salary_Update->setPrenom($prenom);
                                                                $Salary_Update->setPoste($emploi);
                                                                $Salary_Update->setEmailPerso($emailperso);
                                                                $Salary_Update->setEmailPro(NULL);
                                                                $Salary_Update->setMatricule($matricule);
                                                                $num_secu_cless = $numSecu . '' . $cless;
                                                                $Salary_Update->setNumSecu($num_secu_cless);
                                                                $Salary_Update->setIsPaper($bupapier);
                                                                $Salary_Update->setAdresse($adresse);
                                                                $Salary_Update->setVille($ville);
                                                                $Salary_Update->setNatureContrat($contrat);
                                                                $Salary_Update->setZipcode($zipcode);
                                                                $Salary_Update->setDateBegin($date_debut);
                                                                $Salary_Update->setDateEnd($date_fin);
                                                                $Salary_Update->setBirthDay($date_naissance);
                                                                $Salary_Update->setCreatedAt($Salary_Update->getCreatedAt());
                                                                $Salary_Update->setCompany($NewCompany);
                                                                $Salary_Update->setUser($Salary_Update->getUser());
                                                                $db->persist($Salary_Update);
                                                                $db->flush();
                                                                $output->writeln("update avec succes");
                                                                $import->setCommentError('Terminé');
                                                                $import->setProgress(2);
                                                                $db->flush();
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    //ici pour enregistrer plusieur salary avec meme user name
                                    if ($company_id != NULL && $user_id != null && $matricule != NULL) {

                                        $array_fetch_salary_by = $this->fetchExisteSalaryBy($company_id, $user_id, $matricule);
                                        if (count($array_fetch_salary_by) == 0) {
//                                            die('insert + user not null');
                                            $output->writeln("insert + user not null");
                                            // test if num secu existe dans la BDD
                                            $user2 = $repUser->find($user_id);
                                            if ($user2) {
                                                //add New Salary
                                                // insertion new salary
                                                $output->writeln("insertion");
                                                $date_courante = new \Datetime();
                                                $NewSalary = new Salary();
                                                $NewSalary->setUser($user2);
                                                $NewSalary->setNom($nom);
                                                $NewSalary->setPrenom($prenom);
                                                $NewSalary->setPoste($emploi);
                                                $NewSalary->setEmailPerso($emailperso);
                                                $NewSalary->setEmailPro(NULL);
                                                $NewSalary->setMatricule($matricule);
                                                $num_secu_cless = $numSecu . '' . $cless;
                                                $NewSalary->setNumSecu($num_secu_cless);
                                                $NewSalary->setIsPaper($bupapier);
                                                $NewSalary->setAdresse($adresse);
                                                $NewSalary->setVille($ville);
                                                $NewSalary->setNatureContrat($contrat);
                                                $NewSalary->setZipcode($zipcode);
                                                if (is_object($date_naissance)) {
                                                    $NewSalary->setBirthDay($date_naissance);
                                                }
                                                if (is_object($date_debut)) {
                                                    $NewSalary->setDateBegin($date_debut);
                                                }
                                                if (is_object($date_fin)) {
                                                    $NewSalary->setDateEnd($date_fin);
                                                }
                                                $NewSalary->setCreatedAt($date_courante);
                                                $NewSalary->setCompany($get_Company);
                                                $db->persist($NewSalary);
                                                $db->flush();
                                                $output->writeln("insertion avec succes");
                                                $import->setCommentError('Terminé');
                                                $import->setProgress(2);
                                                $db->flush();
                                            }
                                        }
//                                        
                                    }//end if  sur user_id !=null
                                    //ici pour enregistrer plusieur salary avec different user name
                                    if ($company_id != NULL && $user_id == null) {
//                                        die('insert + user null');
                                        $output->writeln("insert + user null");
                                        $plainpasseword = 'aBG976smb77'; //'aBG976smb3'
                                        $salt = uniqid(mt_rand());
                                        $password = hash('sha512', $salt . $plainpasseword);
                                        $Newuser = new User();
                                        $Newuser->setIsActive(false);
                                        $Newuser->setPassword($password);
                                        $Newuser->setSalt($salt);
                                        $Newuser->setUsername($numSecu);
                                        $role = $db->getRepository('FrontBundle:Role')->findOneBy(array('role' => 'ROLE_USER'));
                                        $Newuser->addRole($role);
                                        $db->persist($Newuser);
                                        $output->writeln("insertion");
                                        $date_courante = new \Datetime();
                                        $NewSalary = new Salary();
                                        $NewSalary->setUser($Newuser);
                                        $NewSalary->setNom($nom);
                                        $NewSalary->setPrenom($prenom);
                                        $NewSalary->setPoste($emploi);
                                        $NewSalary->setEmailPerso($emailperso);
                                        $NewSalary->setEmailPro(NULL);
                                        $NewSalary->setMatricule($matricule);
                                        $num_secu_cless = $numSecu . '' . $cless;
                                        $NewSalary->setNumSecu($num_secu_cless);
                                        $NewSalary->setIsPaper($bupapier);
                                        $NewSalary->setAdresse($adresse);
                                        $NewSalary->setVille($ville);
                                        $NewSalary->setNatureContrat($contrat);
                                        $NewSalary->setZipcode($zipcode);
                                        if (is_object($date_naissance)) {
                                            $NewSalary->setBirthDay($date_naissance);
                                        }
                                        if (is_object($date_debut)) {
                                            $NewSalary->setDateBegin($date_debut);
                                        }
                                        if (is_object($date_fin)) {
                                            $NewSalary->setDateEnd($date_fin);
                                        }
                                        $NewSalary->setCreatedAt($date_courante);
                                        $NewSalary->setCompany($get_Company);
                                        $db->persist($NewSalary);
                                        $db->flush();
                                        $output->writeln("insertion avec succes");
                                        $import->setCommentError('Terminé');
                                        $import->setProgress(2);
                                        $db->flush();
                                    }
                                }
                            } // end if is numeric cless
                        } // end if count data = count coldisplay

                        if (count($data) != count($coldisplay)) {
                            if (count($data) > 1) {
                                $output->writeln("coldisplay");
                                $output->writeln(var_dump($coldisplay));
                                $output->writeln("data");
                                $output->writeln(var_dump($data));

                                $import->setCommentError('Problème dans la structure du fichier Attention !!');
                                $import->setProgress(99);
                                $db->flush();
                                die();
//                                $output->writeln("heeeereeeeee");
                                if ((count($data) == 1) && ($trouve_matricule == 1)) {
                                    $import->setCommentError('Problème dans la structure du fichier, des salariés avec des  matricules identique Attention !!');
                                    $import->setProgress(99);
                                    $db->flush();
                                    die();
                                }
                            }
                        }
                        $progress->advance();
                    } // end while
                    $progress->finish();

//                    }
                    // initialisation des variables
                    $tab_search = NULL;
                    $tab_user_id = NULL;
                    $index_search = 0;
                    $trouve_num_secu = 0;
                    $tab_struct_file = NULL;
                    $index_structe_file = 0;
                    $columns_egaux = 0;
                    $tab_search_matricule = NULL;
                    $index_search_matricule = 0;
                    $trouve_matricule = 0;
                    $trouve_company = 0;
                    $array_doublon_ids = NULL;
                    $index_doublon_ids = 0;
                }// fin if handler
            } // fin for import
//            $output->writeln("Load All  imports \n");
//            $imports_load_all = $repImport->findAll();
//            if ($imports_load_all != null) {
//                $output->writeln("load all import not null");
//                
//            }
        }  // fin try
        catch (Exception $e) {
//            $output->writeln("catch");
//            $output->writeln($e);
        }

        $output->writeln("Fin du traitement des imports \n");
        /*         * ******************** Fin de la gestion des imports ********************** */

        // the depots is clean
        /*         * ******************** Gestion des bulletins unitaires ******************** */

        $buErrors = array();
        $bulletins = $rep->findBy(array('signed' => false, 'inProgress' => false, 'disabled' => false, 'error' => false));
        //progress bar
        $progress->start($output, count($bulletins));
        $output->writeln("Début du traitement des bulletins unitaires \n");
        if (count($bulletins) > 0) {
            $buLocation = '/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/unite/';
//            $buLocation = $this->getContainer()->getParameter('kernel.root_dir') . '/../web/uploads/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/unite/';
            $recordId = null;
            $archive_bulletin_unite = 0;
            $filenamezip = '';
            $files = array();
            $index_file_array = 0;
            $source = '';
            $globalmotn = '';
            $globalyear = '';
            $globalday = '';

            $nom = 'all';
            $owner = $this->getContainer()->getParameter('client_id');
            $today = new \DateTime('now');
            $ownerDesc = 'Client id : ' . $owner . ' ' . $today->format('d-m-Y');
            $result = $certSign->createRecord($owner, $ownerDesc);
            // Initialize archive object
//            $zip = new \ZipArchive();
            // Create a new record to store all the docs
            if (is_object($result)) {
                $recordId = $result->createRecordReturn;
            } else {
                exit('Impossible de créer un record');
            }
            if (!is_null($recordId)) {
                foreach ($bulletins as $bulletin) {
                    $month = $bulletin->getMonth();
//                    $matricule = $bulletin->getSalary()->getMatricule();
//                    $output->writeln("month");
//                    $output->writeln(var_dump($month));
                    $year = $bulletin->getYear();
//                    $output->writeln("year");
//                    $output->writeln(var_dump($year));
                    $ext = $bulletin->getExt();
//                    $output->writeln("ext");
//                    $output->writeln(var_dump($ext));
                    $filename = $bulletin->getBulletin(); //"c7691e515f9003ac19312caeda4498ea.pdf"
//                    $output->writeln("filename");
//                    $output->writeln(var_dump($filename));
                    $file = $buLocation . $filename; //"/docs_sign/13/unite/c7691e515f9003ac19312caeda4498ea.pdf"
//                    $output->writeln("file");
//                    $output->writeln(var_dump($file));
//                    $output->writeln("buLocation"); // "/docs_sign/13/unite/"
//                    $output->writeln(var_dump($buLocation));
//                    $output->writeln("printdir"); //"/docs_sign/13/toprint/"
//                    $output->writeln(var_dump($printDir));


                    $salary = $bulletin->getSalary();
                    $bulletin->setCommentError('En cours');
                    $bulletin->setInProgress(true);
                    $db->flush();
                    if (is_object($salary)) {

                        if (!$salary->getIsPaper()) {
                            //Create Document
                            $docName = null;
                            $resDoc = $certSign->addDocument($file, $recordId);
                            if (is_object($resDoc)) {
                                $docName = $resDoc->addDocumentReturn;
//                                $output->writeln("dump docName");
//                                $output->writeln(var_dump($docName));
                            } else {
                                $buErrors[] = 'Impossible d\'ajouter le doc ' . $file . 'au serveur de certification';
                            }
                            // Sign the document
                            if (!is_null($docName)) {
                                $sigName = null;
                                $resSig = $certSign->signDocument($recordId, $docName);
                                if (is_object($resSig)) {
                                    $sigName = $docName;
                                } else {
                                    $buErrors[] = 'Impossible de signer  le doc ' . $docName;
                                }
                                if (!is_null($sigName)) {

                                    $isExt = false;
                                    // Update all obselete bulletins
                                    if (!is_null($ext)) {
                                        $oldSigns = $repDocSign->findBy(array('month' => $month, 'year' => $year, 'salary' => $salary, 'ext' => $ext));
                                        $isExt = true;
                                    } else {
                                        $oldSigns = $repDocSign->findBy(array('month' => $month, 'year' => $year, 'salary' => $salary));
                                    }

                                    foreach ($oldSigns as $os) {
                                        $os->setObsolete(true);
                                    }

                                    // create "Bulletin"
                                    $sigDoc = new SignedDoc();
                                    $sigDoc->setDoc($docName);
                                    $sigDoc->setMonth($month);
                                    $sigDoc->setYear($year);
                                    if ($isExt) {
                                        $sigDoc->setExt($ext);
                                    }
                                    $sigDoc->setRecord($recordId);
                                    $sigDoc->setSignature($sigName);
                                    $sigDoc->setSalary($salary);
                                    $sigsize = $this->getContainer()->getParameter('signature_size');
                                    $size = filesize($file) + $sigsize;
                                    $sigDoc->setSize($size);
                                    $db->persist($sigDoc);

                                    // bulletin
                                    $bulletin->setSigned(true);
                                    $bulletin->setCommentError('Signé');
                                    $bulletin->setInProgress(true);
                                    $db->flush($bulletin);
                                    $archive_bulletin_unite = 1;


                                    $this->getContainer()->enterScope('request');
                                    $this->getContainer()->set('request', new Request(), 'request');
                                    $this->getContainer()->get('email_servies')->getHtmlBulletin($salary, $month, $year);
                                } else {
                                    $buErrors[] = 'Impossible signer  le doc ' . $docName;
                                }
                            } else {
                                $buErrors[] = 'Impossible d\'ajouter le doc ' . $file . 'au serveur de certification';
                            }
                        }
                        $output->writeln("salary is papaer");
                        // Move the file to the directory for print

                        if ($salary->getIsPaper()) {
                            //
                            $month = $bulletin->getMonth();
                            $globalmotn = $month;
                            $year = $bulletin->getYear();
                            $globalyear = $year;

                            $salariesListForPaper[] = $salary;

//                            $output->writeln("file name");
//                            $output->writeln(var_dump($filename));
//                            die('ici');
                            $files[$index_file_array] = $salary->getMatricule() . "." . "pdf";
                            $index_file_array ++;
                            $bulletin->setCommentError('Terminé');
                            $bulletin->setInProgress(true);
                            $db->flush($bulletin);
                        }
                    } else {
                        $buErrors[] = 'Le salarié n\'existe pas ';
                        $bulletin->setCommentError('Erreurs lors de l execution');
                    }

                    // check errors
                    if (count($buErrors) > 0) {
                        $bulletin->setError(true);
//                        $bulletin->setCommentError(implode('<br />', $buErrors));
                        $bulletin->setCommentError('Erreurs lors de l execution');
                    } else {
//                        $bulletin->setInProgress(false);
                    }



                    //Increment progress
                    $progress->advance();
                    $etat_progress_bar = 1;
                    //Supprimer le fichier
//                    unlink($file);
                }// endforeach


                $filenamezip = $nom . '-' . date('d-m-Y-H-i-s') . '.zip';
                $source = $buLocation . $filename; // ==> /docs_sign/13/unite/c7691e515f9003ac19312caeda4498ea.pdf"
                $destination = $printDir . $filenamezip; // $printDir ==> /docs_sign/13/unite/keolis.zip
                $this->Zip2($source, $destination, $files); // $files ==> $files[$index_file_array] = $salary->getMatricule() . "." . "pdf";
                $toPrint = new PrintDepots();
                $toPrint->setFilename($filenamezip);
                $toPrint->setMu(NULL);
                $db->persist($toPrint);
                $output->writeln("persiste toprint avec succeé ");
                $db->flush();
                $output->writeln("enregistrement bu avec succeé ");
                $bulletin->setInProgress(true);
//                $db->persist($bulletin);
                $db->flush($bulletin);
                $output->writeln("enregistrement bu avec succeé ");
                // Send a report email to the manager in the case if there's a doc to print
                if (count($salariesListForPaper) > 0) {
//                    $this->getContainer()->get('email_servies')->sendPrintList($salariesListForPaper, $globalmotn, $globalyear);
                    $this->getContainer()->get('email_servies')->sendPrintList2($salariesListForPaper, $globalmotn, $globalyear, $salariesListForPaper, $filenamezip);
                }
            }
            $certSign->closeRecord($recordId);
//            if ($archive_bulletin_unite === 1) {
//                $certSign->archiveRecord($recordId);
//            }
        }
        $progress->finish();
        $etat_progress_bar = 0;
        $output->writeln("Fin de traitement des bulletins unitaires \n");
        /*         * ****************** Fin du traitement des bulletins unitaires *************** */

        $output->writeln("Début du traitement des fichier ZIP \n");
        $zips = $repZip->findBy(array('signed' => false, 'inProgress' => false, 'disabled' => false, 'error' => false));
        $zipLocation = $zipLocation . '/' . $clientId . '/';

        $zipErrors = array();
        $output->writeln('j0');

        foreach ($zips as $zipfile) {
            $salariesListForPaper = array();
            //unzip
            $output->writeln('avant if');
            if ($zipfile->getInProgress()) {
                $output->writeln('enter if');
                continue;
            }
            $output->writeln('enter foreach');
            $logZip = new LogZip();
            $logZip->setEtape("Début de traitement du fichier " . $zipfile->getName());
            $logZip->setStatut(true);
            $logZip->setZip($zipfile);
            $db->persist($logZip);

            $zipfile->setInProgress(true);
            $db->flush();
            $output->writeln('j0');

            echo 'avant zipname';
            //@TODO Secure the result returns
            $zipname = $zipfile->getName();
            echo 'zipname';
            //echo var_dump($zipname);
            //die('ici zip name');
            //Create a new directory to put all file to print
            $docDir = '/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint/';
//            $docDir = $this->getContainer()->getParameter('kernel.root_dir') . '/../web/uploads/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint/';

            if (!file_exists($docDir)) {
                mkdir($docDir, 0777, true);
                $output->writeln('j0');
            }
            $curDir = $docDir . $zipname;
            if (!file_exists($curDir)) {
                mkdir($curDir, 0777, true);
                $output->writeln('j0');
            }

            $extFile = explode('_', $zipname);
            $prefix = $extFile[0];
            $month = $extFile[1];
            $year = $extFile[2];
            $recordId = null;
            $owner = $this->getContainer()->getParameter('client_id');
            $output->writeln('j0');
            $ownerDesc = 'Client id : ' . $owner . ' ' . implode('/', $extFile);
            $output->writeln('j0');
            $result = $certSign->createRecord($owner, $ownerDesc);
            $output->writeln('j0');
            if (is_object($result)) {

                $recordId = $result->createRecordReturn;


                $output->writeln("result0");
//                $output->writeln(var_dump($result));
                $output->writeln("createRecordReturn0");
//                $output->writeln(var_dump($recordId));

                $output->writeln("l1");

                //Log zip
                $logZip = new LogZip();
                $logZip->setEtape("Création du record $result->createRecordReturn pour le zip " . $zipfile->getName());
                $logZip->setStatut(true);
                $logZip->setZip($zipfile);
                $db->persist($logZip);
            } else {
                //Log zip
                $logZip = new LogZip();
                $logZip->setEtape("Erreur lors de la création du record pour le zip " . $zipfile->getName());
                $logZip->setStatut(false);
                $logZip->setZip($zipfile);
                $db->persist($logZip);

                exit('Impossible de créer un record');
            }
            $output->writeln('j9');
            if (!is_null($recordId)) {
                // unzip
                $file_zip = $zipLocation . $zipname . '.zip';
                $zip = new \ZipArchive();
                $zip->open($file_zip);
                $zip->extractTo($zipLocation);
                $zip->close();
                $error = false;
                $errorDesc = null;
                $dir = $zipLocation . $zipname . DIRECTORY_SEPARATOR;

                $depth = 0;
                $output->writeln('j9');
                // Progress bar
                $fi = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
                $app = new Application();
                $progress = $app->getHelperSet()->get('progress');
                $progress->start($output, iterator_count($fi));
                //Log zip
                $logZip = new LogZip();
                $logZip->setEtape("Debut de traitement des bulletins pour le zip " . $zipfile->getName());
                $logZip->setStatut(true);
                $logZip->setZip($zipfile);
                $db->persist($logZip);
                $output->writeln('j9');
                // Fin progress bar
                if ($handle = opendir($dir)) {
                    $output->writeln('j9');
                    while (false !== ($file = readdir($handle)) && $depth < 1) {
                        if ($file == '.' || $file == '..') {
                            continue;
                        }
                        // if user doesn't exist
                        // if the file is not a pdf ?
                        // if there's a probleme in the zip teatment.
                        $file = $dir . $file;
                        if (!is_dir($file)) {
                            //Get Salary
                            $fullname = basename($file, ".pdf");
                            $fullname = basename($fullname, ".PDF");
                            $fullEx = explode('_', $fullname);
                            $salaryId = $fullEx[0];
                            $output->writeln('j9');

                            if (!is_null($zipfile->getCompany())) {
                                $salary = $repSalary->findOneBy(array('matricule' => $salaryId, 'company' => $zipfile->getCompany()));
                            } else {
                                $salary = $repSalary->findOneBy(array('matricule' => $salaryId));
                            }
                            if (is_object($salary)) {
                                $output->writeln('j9');
                                if (!$salary->getIsPaper()) {
                                    $output->writeln('j9');
                                    $logZip->setEtape("Préparation du bulletin électronique pour le salarié : " . $salary->getId() . ' dans le zip : ' . $zipfile->getName());
                                    $logZip->setStatut(true);
                                    $logZip->setZip($zipfile);
                                    $db->persist($logZip);

                                    //Create Document
                                    $docName = null;
                                    $resDoc = $certSign->addDocument($file, $recordId);
                                    if (is_object($resDoc)) {
                                        $docName = $resDoc->addDocumentReturn;
                                    } else {
                                        //panic mode
                                        $zipErrors[] = 'Impossible d\'ajouter le doc ' . $file . 'au serveur de certification';

                                        //Log zip
                                        $logZip = new LogZip();
                                        $logZip->setEtape("Problème de création du document certeurope pour le bulletin du salarié : " . $salary->getId() . ' dans le zip : ' . $zipfile->getName());
                                        $logZip->setStatut(false);
                                        $logZip->setZip($zipfile);
                                        $db->persist($logZip);

                                        break;
                                    }
                                    // Sign the document
                                    if (!is_null($docName)) {
                                        $sigName = null;
                                        $resSig = $certSign->signDocument($recordId, $docName);
                                        if (is_object($resSig)) {
                                            $sigName = $docName;

                                            //Log zip
                                            $logZip = new LogZip();
                                            $logZip->setEtape("Bulletin signé pour le salarié : " . $salary->getId() . ' dans le zip : ' . $zipfile->getName());
                                            $logZip->setStatut(true);
                                            $logZip->setZip($zipfile);
                                            $db->persist($logZip);
                                        } else {
                                            $zipErrors[] = 'Impossible signer  le doc ' . $docName;

                                            //Log zip
                                            $logZip = new LogZip();
                                            $logZip->setEtape("Problème lors de la signature du bulletin du salarié : " . $salary->getId() . ' dans le zip : ' . $zipfile->getName());
                                            $logZip->setStatut(false);
                                            $logZip->setZip($zipfile);
                                            $db->persist($logZip);

                                            break;
                                        }
                                        if (!is_null($sigName)) {

                                            $oldSigns = $repDocSign->findBy(array('month' => $month, 'year' => $year, 'salary' => $salary));
                                            foreach ($oldSigns as $os) {
                                                $os->setObsolete(true);
                                            }

                                            // create "Bulletin"
                                            $sigDoc = new SignedDoc();
                                            $sigDoc->setDoc($docName);
                                            $sigDoc->setMonth($month);
                                            $sigDoc->setYear($year);
                                            $sigDoc->setRecord($recordId);
                                            $sigDoc->setSignature($sigName);
                                            $sigDoc->setSalary($salary);
                                            $sigsize = $this->getContainer()->getParameter('signature_size');
                                            $size = filesize($file) + $sigsize;
                                            $sigDoc->setSize($size);
                                            $db->persist($sigDoc);
                                            $db->flush();

                                            //Log zip
                                            $logZip = new LogZip();
                                            $logZip->setEtape("Bulletin sauvegardé pour le salarié : " . $salary->getId() . ' dans le zip : ' . $zipfile->getName());
                                            $logZip->setStatut(true);
                                            $logZip->setZip($zipfile);
                                            $db->persist($logZip);

                                            // unlink file
                                            $this->getContainer()->enterScope('request');
                                            $this->getContainer()->set('request', new Request(), 'request');
                                            $this->getContainer()->get('email_servies')->getHtmlBulletin($salary, $month, $year);
                                        } else {
                                            // stop the process and save the error
                                            $zipErrors[] = 'Impossible signer  le doc ' . $docName;
                                            //Log zip
                                            $logZip = new LogZip();
                                            $logZip->setEtape("Problème lors de la signature du bulletin du salarié : " . $salary->getId() . ' , doc :' . $docName . ' dans le zip : ' . $zipfile->getName());
                                            $logZip->setStatut(false);
                                            $logZip->setZip($zipfile);
                                            $db->persist($logZip);
                                            break;
                                        }
                                    } else {
                                        $zipErrors[] = 'Impossible d\'ajouter le doc ' . $file . 'au serveur de certification';
                                        //Log zip
                                        $logZip = new LogZip();
                                        $logZip->setEtape("Problème lors de l'ajout du document : $file , Salarié : " . $salary->getId() . ' dans le zip : ' . $zipfile->getName());
                                        $logZip->setStatut(false);
                                        $logZip->setZip($zipfile);
                                        $db->persist($logZip);
                                        break;
                                    }
                                } else {

                                    copy($file, $printDir . $fullname . '.pdf');
                                    //Log zip
                                    $logZip = new LogZip();
                                    $logZip->setEtape("Bulletin à imprimer pour le salarié : " . $salary->getId() . ' dans le zip : ' . $zipfile->getName());
                                    $logZip->setStatut(true);
                                    $logZip->setZip($zipfile);
                                    $db->persist($logZip);
                                    // Add salary to the list of manual treatement
                                    $salariesListForPaper[] = $salary;
                                }
                            } else {
                                $zipErrors[] = 'Salarié innexistant par rapport au fichier  ' . $file;
                                //Log zip
                                $logZip = new LogZip();
                                $logZip->setEtape("Le salarié lié au document : $file n'existe pas dans le zip : " . $zipfile->getName());
                                $logZip->setStatut(false);
                                $logZip->setZip($zipfile);
                                $db->persist($logZip);
                                break;
                            }
                        } else {
                            $zipErrors[] = 'Le fichier ' . $file . ' n\'existe pas !';
                            //Log zip
                            $logZip = new LogZip();
                            $logZip->setEtape("Le fichier : $file n'existe pas dans le zip : " . $zipfile->getName());
                            $logZip->setStatut(false);
                            $logZip->setZip($zipfile);
                            $db->persist($logZip);
                            break;
                        }
                        $progress->advance();
                        $etat_progress_bar = 1;
                    }
                    closedir($handle);
                }

                //Log zip
                $logZip = new LogZip();
                $logZip->setEtape("Archivage du zip : " . $zipfile->getName());
                $logZip->setStatut(true);
                $logZip->setZip($zipfile);
                $db->persist($logZip);

                echo 'closing';
                //Fermer le record
                $certSign->closeRecord($recordId);
                echo 'archiving';
                echo $recordId;
                //Archiver le record
//                $certSign->archiveRecord($recordId);
                //unlink the zip file
//                unlink($file_zip);
//                $output->writeln('$file_zip');
                //Remove all the extracted directory
//                $this->deleteDirectory($dir);
//                $output->writeln('delete directory1');
                // Fin de traitement
                if (count($zipErrors) > 0) {
                    $zipfile->setError(true);
                    $zipfile->setCommentError(implode('<br />', $zipErrors));
                } else {
                    $zipfile->setSigned(true);
                    $zipfile->setInProgress(false);
                }
                $db->flush();
            } else {
                //Log zip
                $logZip->setEtape("Problème du record lors du traitemnet du zip : " . $zipfile->getName());
                $logZip->setStatut(true);
                $logZip->setZip($zipfile);
                $db->persist($logZip);
            }
            $progress->finish();
            $etat_progress_bar = 0;
            // Send Email report
            if (count($salariesListForPaper) > 0) {
                $this->getContainer()->get('email_servies')->sendPrintList($salariesListForPaper, $month, $year);
            }
        }

        $output->writeln("Début du traitement des depots \n");
//        die('hounnni');
        //http://stackoverflow.com/questions/1334398/how-to-delete-a-folder-with-contents-using-php


        try {
            $output->writeln("l1");
            $clientId = $this->getContainer()->getParameter('client_id');
            $output->writeln("l2");

            $depots = $repMu->findBy(array('signed' => false, 'inProgress' => false, 'disabled' => false, 'error' => false, 'verified' => true));

            foreach ($depots as $depot) {
                $depot->setInProgress(true);
                $db->persist($depot);
            }
            $db->flush();

            $output->writeln("l3");
            $muErrors = array();
            $to_archive = 0;
            $etat_progress_bar = 0;
            foreach ($depots as $depot) {
                $output->writeln("enter foreach depots");
                $salariesListForPaper = array();
                $index_salariesListForPaper = 0;
                $salariesPrint2p = array();
                $salariesPrint1p = array();
                $array_pdf_to_zip = array();
                $index_pdf_to_zip = 1;
                $array_path_bulltin_papier = array();
                $index_path_bulltin_papier = 1;
                $path_abolue = "";
                $globalDir = '';
                $folder_parent_name = '';
                $_array_file_pdf = array();
                $_index_file_pdf = 1;
                //unzip
                $output->writeln("avant if depots");

                $output->writeln("apres if depots progress");
                $zipLocation = $this->getContainer()->getParameter('zip_location');
                $output->writeln("l4");

                $dir = $zipLocation . '/files/' . $clientId . '/' . $depot->getUidUpload() . '/';
                $output->writeln("dir");
//                $output->writeln(var_dump($dir));
//                die('dir');
                $output->writeln("l5");

                $logMu = new LogMu();
                $logMu->setEtape("Début de traitement du fichier " . $depot->getId());
                $logMu->setStatut(true);
                $logMu->setMu($depot);
                $db->persist($logMu);
                $db->flush();
                $output->writeln("l1");

                //create a directory in the name of entity if it doesn't exist
                $nom = 'all';
                if (!is_null($depot->getCompany())) {
                    $nom = $depot->getCompany()->getNom();
                }


                $month = $depot->getMonth();
                $year = $depot->getYear();
                $recordId = null;
                $owner = $this->getContainer()->getParameter('client_id');
                $output->writeln("l1");

                $ownerDesc = 'Client id : ' . $owner . ' ' . $depot->getId();
                $result = $certSign->createRecord($owner, $ownerDesc);
                $output->writeln("l1");

                if (is_object($result)) {

                    $output->writeln("result");
//                    $output->writeln(var_dump($result));
                    $recordId = $result->createRecordReturn;
                    $output->writeln("createRecordReturn");
//                    $output->writeln(var_dump($recordId));
                    $output->writeln("l1");


                    //Log zip
                    $logMu = new LogMu();
                    $logMu->setEtape("Création du record $result->createRecordReturn pour le dépôt " . $depot->getId());
                    $logMu->setStatut(true);
                    $logMu->setMu($depot);
                    $db->persist($logMu);
                } else {

                    $logMu = new LogMu();
                    $logMu->setEtape("Erreur lors de la création du record pour le dépôt " . $depot->getName());
                    $logMu->setStatut(true);
                    $logMu->setMu($depot);
                    $db->persist($logMu);
                    $output->writeln("l1");

                    exit('Impossible de créer un record');
                }
                if (!is_null($recordId)) {
                    // unzip
                    // Progress bar
                    // ici il faut ajouter une condition si progress bar elle est encore actif et on du traittement alors
                    // il faut pas instancier du nouveau
                    $output->writeln("avant FilesystemIterator");
                    $fi = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
                    $app = new Application();
                    $progress = $app->getHelperSet()->get('progress');
                    $progress->start($output, iterator_count($fi));
//                    $progress->start($output, count($depots));
                    $etat_progress_bar = 1;
                    $depth = 0;
                    $output->writeln("l1");

                    //Log depots
                    $logMu = new LogMu();
                    $output->writeln("l7");
                    $logMu->setEtape("Debut de traitement des bulletins pour le dépôt " . $logMu->getId());
                    $output->writeln("l8");
                    $logMu->setStatut(true);
                    $logMu->setMu($depot);
                    $db->persist($logMu);
                    $output->writeln("l9");

                    // Fin progress bar
                    if ($handle = opendir($dir)) {
                        while (false !== ($file = readdir($handle)) && $depth < 1) {
                            $output->writeln("l10");
                            if ($file == '.' || $file == '..') {
                                continue;
                            }
                            $output->writeln("opendir + enter while");

                            // if user doesn't exist
                            // if the file is not a pdf ?
                            // if there's a probleme in the zip teatment.
                            $file = $dir . $file;
                            $output->writeln("file");
                            $output->writeln(var_dump($file));
                            if (!is_dir($file)) {
                                //Get Salary
                                $output->writeln("enter ! is_dir file");
                                $path_parts = pathinfo($file);
                                $extension = $path_parts['extension'];
                                $fullname = str_replace(' ', '', $path_parts['filename']);
//                                $preg_replace = preg_replace('/\s+/', '', $path_parts['filename']);
//                                $output->writeln("<------------preg_replace --------->!!!!!!!!!!");
//                                $output->writeln(var_dump($preg_replace));
                                $fullEx = explode('_', $fullname);
                                $salaryId = $fullEx[0];
                                //$salaryId = $fullname;
                                if (!is_null($depot->getCompany())) {
                                    $salary = $repSalary->findOneBy(array('matricule' => $salaryId, 'company' => $depot->getCompany()));
                                } else {
                                    $salary = $repSalary->findOneBy(array('matricule' => $salaryId));
                                }
                                $output->writeln("load salary");
                                if (is_object($salary)) {
                                    $output->writeln(" salary is object");
                                    if (!$salary->getIsPaper()) {
                                        $output->writeln(" salary is paper");
                                        $logMu = new LogMu();
                                        $logMu->setEtape("Préparation du bulletin électronique pour le salarié : " . $salary->getId() . ' dans le dépôt : ' . $depot->getId());
                                        $logMu->setStatut(true);
                                        $logMu->setMu($depot);
                                        $db->persist($logMu);

                                        //Create Document
                                        $docName = null;
                                        $resDoc = $certSign->addDocument($file, $recordId);
                                        if (is_object($resDoc)) {
                                            $docName = $resDoc->addDocumentReturn;
                                        } else {
                                            //panic mode
                                            $muErrors[] = 'Impossible d\'ajouter le doc ' . $file . 'au serveur de certification';

                                            //Log zip
                                            $logMu = new LogMu();
                                            $logMu->setEtape("Problème de création du document certeurope pour le bulletin du salarié : " . $salary->getId() . ' dans le dépôt : ' . $depot->getId());
                                            $logMu->setStatut(false);
                                            $logMu->setMu($depot);
                                            $db->persist($logMu);

                                            break;
                                        }
                                        // Sign the document
                                        if (!is_null($docName)) {
                                            $sigName = null;
                                            $resSig = $certSign->signDocument($recordId, $docName);
                                            if (is_object($resSig)) {
                                                $sigName = $docName;

                                                //Log zip
                                                $logMu = new LogMu();
                                                $logMu->setEtape("Bulletin signé pour le salarié : " . $salary->getId() . ' dans le dépôt : ' . $depot->getId());
                                                $logMu->setStatut(true);
                                                $logMu->setMu($depot);
                                                $db->persist($logMu);
                                            } else {
                                                $muErrors[] = 'Impossible signer  le doc ' . $docName;

                                                //Log zip
                                                $logMu = new LogMu();
                                                $logMu->setEtape("Problème lors de la signature du bulletin du salarié : " . $salary->getId() . ' dans le dépôt : ' . $depot->getId());
                                                $logMu->setStatut(false);
                                                $logMu->setMu($depot);
                                                $db->persist($logMu);

                                                break;
                                            }
                                            $output->writeln("l19");
                                            if (!is_null($sigName)) {
                                                $isExt = false;
                                                $oldSigns = $repDocSign->findBy(array('month' => $month, 'year' => $year, 'salary' => $salary));
                                                foreach ($oldSigns as $key => $os) {
                                                    //Supprimer l'option obsolete
                                                    //$os->setObsolete(true);
                                                    $isExt = true;
                                                    $lastExt = $key + 1;
                                                    $os->setExt('ext' . $lastExt);
                                                }
                                                $output->writeln("l20");



                                                // create "Bulletin"
                                                $sigDoc = new SignedDoc();
                                                $sigDoc->setDoc($docName);
                                                $sigDoc->setMonth($month);
                                                $sigDoc->setYear($year);
                                                $output->writeln("l21");
                                                if ($isExt) {
                                                    $sigDoc->setExt('ext' . ($lastExt + 1));
                                                }

                                                $sigDoc->setRecord($recordId);
                                                $sigDoc->setSignature($sigName);
                                                $sigDoc->setSalary($salary);
                                                $sigsize = $this->getContainer()->getParameter('signature_size');
                                                $size = filesize($file) + $sigsize;
                                                $sigDoc->setSize($size);
                                                $db->persist($sigDoc);
                                                $db->flush();
                                                $to_archive = 1;
                                                $output->writeln("l22");

                                                //Log zip
                                                $logMu = new LogMu();
                                                $logMu->setEtape("Bulletin sauvegardé pour le salarié : " . $salary->getId() . ' dans le dépôt : ' . $depot->getId());
                                                $logMu->setStatut(true);
                                                $logMu->setMu($depot);
                                                $db->persist($logMu);
                                                $output->writeln("l23");


                                                // unlink file
                                                $this->getContainer()->enterScope('request');
                                                $this->getContainer()->set('request', new Request(), 'request');
                                                $this->getContainer()->get('email_servies')->getHtmlBulletin($salary, $month, $year);
                                            } else {
                                                // stop the process and save the error
                                                $muErrors[] = 'Impossible signer  le doc ' . $docName;
                                                //Log zip
                                                $logMu = new LogMu();
                                                $logMu->setEtape("Problème lors de la signature du bulletin du salarié : " . $salary->getId() . ' , doc :' . $docName . ' dans le dépôt : ' . $depot->getId());
                                                $logMu->setStatut(false);
                                                $logMu->setMu($depot);
                                                $db->persist($logMu);

                                                break;
                                            }
                                            $output->writeln("l24");
                                        } else {
                                            $muErrors[] = 'Impossible d\'ajouter le doc ' . $file . 'au serveur de certification';
                                            //Log zip
                                            $logMu = new LogMu();
                                            $logMu->setEtape("Problème lors de l'ajout du document : $file , Salarié : " . $salary->getId() . ' dans le dépôt : ' . $depot->getId());
                                            $logMu->setStatut(false);
                                            $logMu->setMu($depot);
                                            $db->persist($logMu);
                                            break;
                                        }
                                        $output->writeln("l25");
                                    } else {

                                        $output->writeln("salary is  paper");
                                        //check if the document contain one or two pages or more
                                        $_array_file_pdf[$_index_file_pdf] = $file;
                                        $_index_file_pdf ++;
                                        //Log zip
                                        $logMu = new LogMu();
                                        $logMu->setEtape("Bulletin à imprimer pour le salarié : " . $salary->getId() . ' dans le dépôt : ' . $depot->getId());
                                        $logMu->setStatut(true);
                                        $logMu->setMu($depot);
                                        $db->persist($logMu);
                                        // Add salary to the list of manual treatement
                                        $salariesListForPaper[$index_salariesListForPaper] = $salary;
                                        $index_salariesListForPaper ++;
                                    }
                                    $output->writeln("l27");
                                } else {
                                    $muErrors[] = 'Salarié innexistant par rapport au fichier  ' . $file;
                                    //Log zip
                                    $logMu = new LogMu();
                                    $logMu->setEtape("Le salarié lié au document : $file n'existe pas dans le dépôt : " . $depot->getId());
                                    $logMu->setStatut(false);
                                    $logMu->setMu($depot);
                                    $db->persist($logMu);
                                    break;
                                }
                                $output->writeln("l28");
                            } else {
                                $muErrors[] = 'Le fichier ' . $file . ' n\'existe pas !';
                                //Log zip
                                $logMu = new LogMu();
                                $logMu->setEtape("Le fichier : $file n'existe pas dans le dépôt : " . $depot->getId());
                                $logMu->setStatut(false);
                                $logMu->setMu($depot);
                                $db->persist($logMu);
                                break;
                            }
                            $output->writeln("l29");
                            $progress->advance();
                        }
                        $output->writeln("l30");
                        closedir($handle);
                        $output->writeln("l31");
                    }
                    if (is_dir($dir)) {
                        $output->writeln("enter if is_dir for ziping");
                        //1-create folder to zip
                        $folder_parent_name = '';
                        $filenamezip = "";
                        if (count($_array_file_pdf) > 0) {
                            $folder_parent_name = $nom . '-' . date('d-m-Y-H-i-s');
                            $output->writeln("<-----------------create folder_parent_name to zip------------>");
                            $output->writeln(var_dump($folder_parent_name));
                            if ($folder_parent_name != '') {
                                $this->FolderToZipContainPdfFile($_array_file_pdf, $folder_parent_name);
                            }
                            //2-zip folder
                            if (count($salariesListForPaper) > 0) {
                                $filenamezip = $folder_parent_name . '.' . 'zip';
                                $output->writeln("<--------------filenamezip--------------->");
                                $output->writeln(var_dump($filenamezip));
                                $_path_folder_to_zip = '/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint/' . $filenamezip;
                                // Create recursive directory iterator
                                $zip = new \ZipArchive();
                                $zip->open($_path_folder_to_zip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                                $folder_contain_file_to_zip = '/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint/' . $folder_parent_name;
                                // Get real path for our folder
                                $rootPath_for_papier = realpath($folder_contain_file_to_zip); // ici on donne le chemin des bulletin en papier
                                $files = new \RecursiveIteratorIterator(
                                        new \RecursiveDirectoryIterator($rootPath_for_papier), \RecursiveIteratorIterator::LEAVES_ONLY
                                );
                                foreach ($files as $name => $file) {
                                    // Skip directories (they would be added automatically)
                                    if (!$file->isDir()) {
                                        // Get real and relative path for current file
                                        $filePath = $file->getRealPath();
                                        $relativePath = substr($filePath, strlen($rootPath_for_papier) + 1);
                                        // Add current file to archive
                                        $zip->addFile($filePath, $relativePath);
                                    }
                                }
                                // Zip archive will be created only after closing object
                                $zip->close();
                                $output->writeln("<-----------------success zip folder------------>");
                            }
                        }
                        $output->writeln("<-----------------create link for dawnload zip------------>");
                        if (count($salariesListForPaper) > 0) {
                            if ($filenamezip != "") {
                                $toPrint = new PrintDepots();
                                $toPrint->setFilename($filenamezip);
                                $toPrint->setMu($depot);
                                $db->persist($toPrint);
                            }
                        }
                        if (count($_array_file_pdf) > 0) {
                            //3- delete folder after zip
                            $_cuurent_target = '/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint';
                            $targer_folder_after_zip = $_cuurent_target . '/' . $folder_parent_name;
                            $output->writeln("<-----------------name folder to delete------------>");
                            $output->writeln(var_dump($folder_parent_name));
                            if (is_dir($targer_folder_after_zip)) {
                                $_bool_delete_folder_zip = $this->Delete($targer_folder_after_zip);

                                if ($_bool_delete_folder_zip) {
                                    $output->writeln("success delete");
                                } else {
                                    $output->writeln("echec delete");
                                }
                            }// end if 
                        }
                    }
                    // Log zip
                    $logMu = new LogMu();
                    $logMu->setEtape("Archivage du zip : " . $depot->getId());
                    $logMu->setStatut(true);
                    $logMu->setMu($depot);
                    $db->persist($logMu);

                    echo 'closing';
                    //Fermer le record
                    $certSign->closeRecord($recordId);
                    echo 'archiving';
                    //Archiver le record
                    echo $recordId;
                    echo '$certSign';
                    // Fin de traitement
                    if (count($muErrors) > 0) {
                        $depot->setError(true);
                        $depot->setCommentError(implode('<br />', $muErrors));
                    } else {
                        $depot->setSigned(true);
                        $depot->setInProgress(false);
                    }
                    $db->flush();
                } else {
                    //Log zip
                    $logMu->setEtape("Problème du record lors du traitemnet du zip : " . $depot->getId());
                    $logMu->setStatut(true);
                    $logMu->setMu($depot);
                    $db->persist($logMu);
                }
                $progress->finish();
                // Send Email report
                if (count($salariesListForPaper) > 0) {

                    $output->writeln("salariesListForPaper");
                    $output->writeln(var_dump(count($salariesListForPaper)));

                    if (count($salariesListForPaper) > 0) {
                        $this->getContainer()->get('email_servies')->sendPrintList2($salariesListForPaper, $month, $year, $salariesListForPaper, $filenamezip);

//                        $_max_ligne_par_mail = 100;
//                        $devision = count($salariesListForPaper) / $_max_ligne_par_mail;
//                        $number = explode('.', $devision);
//                        if (count($number) > 1) {
//                            $number1 = $number[0];
//                            $number2 = $number[1];
//                            $nb_page_par_mail = $number[0] + 1;
//
//                            $output->writeln("nb_page_par_mail");
//                            $output->writeln(var_dump($nb_page_par_mail));
//                        } elseif (count($number) === 1) {
//                            $nb_page_par_mail = $number[0];
//                            $output->writeln("nb_page_par_mail");
//                            $output->writeln(var_dump($nb_page_par_mail));
//                        }
                    }
//                    die('icicicii');
                }
            }// end foreach 


            $output->writeln("Fin du traitement des depots \n");
            $output->writeln("Update Stats \n");
            $stats = $this->getContainer()->get('fulloffice.stat')->getStats();
            $repStat = $db->getRepository('BackBundle:Stat');
            $stat = $repStat->find(1);
            if (!is_object($stat)) {
                $stat = new Stat();
            }
            $stat->setSizeBulletins($stats['esize']);
            $stat->setSizeDocs($stats['lsize']);
            $stat->setUpdateAt(new \DateTime());

            $db->persist($stat);
            $db->flush();
            //$progress->finish();
            $output->writeln("\nTraitement des signatures est terminé avec succès");
        } catch (Exception $ex) {
            
        }
    }

    private function ArchiveBackupZip() {
        $zip = new \ZipArchive();
        if ($zip->open('blablabla.zip', \ZipArchive::CREATE) === TRUE) {

//            var_dump('enter if zip open');
//KEOLIS NIMES-07-05-2017-02-57-20
//            var_dump(new \DirectoryIterator('/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint/blablabla/'));
            //new DirectoryIterator('Mails/')
            //'/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint'
            foreach (new \DirectoryIterator('/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint/blablabla/') as $fileInfo) {
                if (in_array($fileInfo->getFilename(), [".", ".."])) {
                    continue;
                }
                $fileName = $fileInfo->getPathname();

                $zip->addFile($fileName);
//                var_dump('$zip');
//                  var_dump($zip);
//                die('end $zip');
            }

            $zip->close();
        }
    }

    private function getPDFPages($document) {
//        var_dump($document);
//        die('document');
//        $cmd = "/var/www/xpdfbin-linux-3.04/bin32/pdfinfo";           // Linux
        $_dynamique_root = realpath(__DIR__ . '/../../../..'); //===> /var/www (in project architecture symfony2)
//        $cmd = $_dynamique_root . "/xpdfbin-linux-3.04/bin32/pdfinfo";// bin32 ==> pour localhost
        $cmd = $_dynamique_root . "/xpdfbin-linux-3.04/bin64/pdfinfo"; // bin32 ==> pour localhost
//        $cmd = "C:\\path\\to\\pdfinfo.exe";  // Windows
        // Parse entire output
        // Surround with double quotes if file name has spaces
        exec("$cmd \"$document\"", $output);

        // Iterate through lines
        $pagecount = 0;
        foreach ($output as $op) {
            // Extract the number
            if (preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
                $pagecount = intval($matches[1]);
                break;
            }
        }

        return $pagecount;
    }

    private function FolderToZipContainPdfFile($_array_file_pdf_param, $name_folder_parent_param) {
        $_array_file_pdf = $_array_file_pdf_param;
        foreach ($_array_file_pdf as $key => $value) {

            $path_parts_file_pdf = pathinfo($value);
            $bulltin_pdf = $path_parts_file_pdf['basename'];
            //teste nbr page in file pdf
            $targer_folder_to_zip = '/docs_sign/' . $this->getContainer()->getParameter('client_id') . '/toprint';
            $nom_folder_parent = $name_folder_parent_param; //$nom . '-' . date('d-m-Y-H-i-s');
            $nbpages = $this->getPDFPages($value);
            $nom_folder_childe = $nbpages . '_page'; //$nbpages
            $target_folder_child = $targer_folder_to_zip . '/' . $nom_folder_parent;
            $folder_parent_existe = $targer_folder_to_zip . '/' . $nom_folder_parent;
            $folder_children_existe = $target_folder_child . '/' . $nom_folder_childe;
            if (is_dir($targer_folder_to_zip)) {
                if (!file_exists($folder_parent_existe)) {
                    mkdir($targer_folder_to_zip . '/' . $nom_folder_parent, 0777);
                }// end if folder parent not existe
                if ($nbpages > 0) {//$nbpages
                    if (file_exists($folder_parent_existe)) {
                        if (is_dir($target_folder_child . '/')) {
                            if (!file_exists($folder_children_existe)) {
                                mkdir($target_folder_child . '/' . $nom_folder_childe, 0777);
                            }
                        }
                    }
                }
            }
            $source_file_pdf = $value; //"/home/boussacsou/Bureau/20001.pdf";
            $dest_folder = $target_folder_child . '/' . $nom_folder_childe . '/' . $bulltin_pdf;
            if ($nbpages > 0) {
                copy($source_file_pdf, $dest_folder);
            }
        }
    }

    private function folderExist($folder) {
        // Get canonicalized absolute pathname
        $path = realpath($folder);

        // If it exist, check if it's a directory
        if ($path !== false AND is_dir($path)) {
            // Return canonicalized absolute pathname
            return $path;
        }

        // Path/folder does not exist
        return false;
    }

    private function deleteDirectory($directory) {
        //echo var_dump($directory);
        try {
            if (!$dh = @opendir($directory)) {
                return false;
                echo ' 1';
            }
            while ($file = readdir($dh)) {
                if ($file == "." || $file == "..") {
                    echo ' 2';
                    continue;
                }
                echo ' 3';
                if (is_dir($directory . "/" . $file)) {
                    echo ' 4';
                    $this->deleteDirectory($directory . "/" . $file);
                    echo ' 5';
                }
                if (is_file($directory . "/" . $file)) {
                    echo ' 6';
                    unlink($directory . "/" . $file);
                    echo ' 7';
                }
                echo ' 8';
            }
            echo ' 9';
            closedir($dh);
            echo ' 10';
            rmdir($directory);
            echo ' 11';
        } catch (Exception $ex) {
            // echo 'ex';
            echo '"ex"';
//            echo var_dump($ex);
        }
    }

    private function zip($source, $destination) {
        try {
            if (!extension_loaded('zip') || !file_exists($source)) {
                return false;
            }

            $zip = new \ZipArchive();
            if (!$zip->open($destination, \ZipArchive::CREATE)) {
                return false;
            }

            $source = str_replace('\\', '/', realpath($source));

            if (is_dir($source) === true) {
                $files = new \RecursiveDirectoryIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

                foreach ($files as $file) {
                    $file = str_replace('\\', '/', $file);

                    // Ignore "." and ".." folders
                    if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                        continue;

                    $file = realpath($file);

                    if (is_dir($file) === true) {
                        $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                    } else if (is_file($file) === true) {
                        $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                    }
                }
            } else if (is_file($source) === true) {
                $zip->addFromString(basename($source), file_get_contents($source));
            }

            return $zip->close();
        } catch (Exception $ex) {
            //echo 'ex';
            // echo var_dump($ex);
            // echo '"ex"';
        }
    }

    private function randomUsername($valid_chars, $length) {
        // start with an empty random string
        $random_string = "";

        // count the number of chars in the valid chars string so we know how many choices we have
        $num_valid_chars = strlen($valid_chars);

        // repeat the steps until we've created a string of the right length
        for ($i = 0; $i < $length; $i++) {
            // pick a random number from 1 up to the number of valid chars
            $random_pick = mt_rand(1, $num_valid_chars);

            // take the random character out of the string of valid chars
            // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
            $random_char = $valid_chars[$random_pick - 1];

            // add the randomly-chosen char onto the end of our string so far
            $random_string .= $random_char;
        }

        // return our finished random string
        return $random_string;
    }

    function Zip2($source, $destination, $array_file_to_print) {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }
//        $valid_files = array();

        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZipArchive::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;

                $file = realpath($file);
//                var_dump("file");
//                var_dump($file);

                if (is_dir($file) === true) {
//                    var_dump("enter if is_dir");
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
//                    var_dump("zip->addEmptyDir");
//                    var_dump($zip->addEmptyDir(str_replace($source . '/', '', $file . '/')));
//                    var_dump("file");
//                    var_dump($file);
                } else if (is_file($file) === true) {
//                    var_dump("enter else if is_file(file) true");
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
//                    var_dump("zip->addFromString1");
//                    var_dump($zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file)));
//                    var_dump("str replace source");
//                    var_dump(str_replace($source . '/', '', $file));
//                    var_dump(" file_get_contents file");
//                    var_dump(file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
//            var_dump("enter else if is_file(source) true");
            foreach ($array_file_to_print as $key => $value) {
//                var_dump("value");
//                var_dump($value);
                $zip->addFromString($value, file_get_contents($source));
//                var_dump("zip->addFromString foreach file");
//                var_dump($zip->addFromString($value, file_get_contents($source)));
            }
//            $zip->addFromString(basename($source), file_get_contents($source));
//            var_dump("zip->addFromString2");
//            var_dump($zip->addFromString(basename($source), file_get_contents($source)));
//            var_dump("basename source");
//            var_dump(basename($source));
//            var_dump("file_get_contents source");
//            var_dump(file_get_contents($source));
//            die('zip');
        }


//        foreach ($valid_files as $file) {
//            $new_filename = substr($file, strrpos($file, '/') + 1);
//            $zip->addFile($file, $new_filename);
//       
//        };


        return $zip->close();
    }

    function Delete($path) {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                $this->Delete(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }

    public function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    function Zip3($files = array(), $destination = '', $overwrite = true) {

        if (file_exists($destination) && !$overwrite) {
            return false;
        }
        $valid_files = array();
        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $valid_files[] = $file;
                }
            }
        }
        if (count($valid_files)) {
            $zip = new \ZipArchive();
            if ($zip->open($destination, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            foreach ($valid_files as $file) {
                $new_filename = substr($file, strrpos($file, '/') + 1);
                $zip->addFile($file, $new_filename);
            }
            $zip->close();
            return file_exists($destination);
        } else {
            return false;
        }
    }

    public function fetchExisteSalaryBy($company_id, $user_id, $matricule) {
//        $query = "SELECT * FROM propara_test.salary ";
        $db_name = $this->getContainer()->getParameter('database_name');
        $query = "SELECT * FROM " . $db_name . " " . ".salary  ";
        $query .= ' ';
        $query_where = "where  company_id ='" . $company_id . "'";
        $query_where .= ' ';
        $query_and = "and  user_id ='" . $user_id . "'";
        $query_and .= ' ';
        $query_and .= "and  matricule ='" . $matricule . "'";
        $query_and .= ' ';
        $queryfinale = $query . $query_where . $query_and;

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare($queryfinale);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
