<?php

namespace FrontBundle\Service;

/*
 * This file is part of the fulldon project
 *
 * (c) SAMI BOUSSACSOU <boussacsou@intersa.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\ORM\EntityManager;
use FrontBundle\Entity\Salary;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;

class EmailService extends ContainerAware {

    private $logo;
    private $perso;

    public function __construct(EntityManager $em) {
        $this->em = $em;
        $persoRep = $this->em->getRepository('BackBundle:Personnalisation');
        $this->perso = $persoRep->find(1);
    }

    public function sendActivation(Salary $salary, $lienActivation) {
        if (is_object($this->perso)) {
            $this->logo = '<img src="' . $this->container->getParameter('url_site') . '/uploads/' . $this->perso->getLogo() . '" width="60" />';
        }
//        $email = $salary->getEmailPerso();
        $email = 'turki@intersa.fr';
        if (is_null($email)) {
//            $email = $salary->getEmailPro();
            $email = 'turki@intersa.fr';
        }
        $nom = $salary->getNom();
        $prenom = $salary->getPrenom();
        $template = $this->perso->getEmailActivation();
        $objet = $this->perso->getObjMailActivation();
        $login = $salary->getUser()->getUsername();
        $template = str_replace('{{nom}}', "$nom", $template);
        $template = str_replace('{{prenom}}', "$prenom", $template);
        $template = str_replace('{{logo}}', "$this->logo", $template);
        $template = str_replace('{{login}}', "$login", $template);
        preg_match_all('/lien_activation\[([^]]+)\]/', $template, $matches);

        //Html of link Activation
        foreach ($matches[1] as $linkName) {

            if (!empty($linkName)) {
                $lienActivationHtml = '<a href="' . $lienActivation . '">' . $linkName . '</a>';
                $template = str_replace("{{lien_activation[$linkName]}}", "$lienActivationHtml", $template);
            }
            $template = str_replace('{{lien_activation}}', "$lienActivation", $template);
        }

        $this->container->get('login.mailer')->sendMail($objet, $email, $template);
    }

    public function sendRelance(Salary $salary) {
        $nom = $salary->getNom();
        $prenom = $salary->getPrenom();
        $login = $salary->getUser()->getUsername();
        $lienConnexion = $this->container->getParameter('url_site');
        $objet = $this->perso->getObjMailRelance();
        $template = $this->perso->getEmailRelance();

//        $email = $salary->getEmailPerso();
        $email = 'turki@intersa.fr';
        if (is_null($email)) {
//            $email = $salary->getEmailPro();
            $email = 'turki@intersa.fr';
        }

        $template = str_replace('{{nom}}', "$nom", $template);
        $template = str_replace('{{prenom}}', "$prenom", $template);
        $template = str_replace('{{logo}}', "$this->logo", $template);
        $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        $template = str_replace('{{login}}', "$login", $template);
        preg_match_all('/lien_connexion\[([^]]+)\]/', $template, $matches);

        //Html of link Activation
        foreach ($matches[1] as $linkName) {

            if (!empty($linkName)) {
                $lienConnexionHtml = '<a href="' . $lienConnexion . '">' . $linkName . '</a>';
                $template = str_replace("{{lien_connexion[$linkName]}}", "$lienConnexionHtml", $template);
            }
            $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        }
        $this->container->get('login.mailer')->sendMail($objet, $email, $template);
    }

    public function sendAfterActivation(Salary $salary) {
        if (is_object($this->perso)) {
            $this->logo = '<img src="' . $this->container->getParameter('url_site') . '/uploads/' . $this->perso->getLogo() . '" width="60" />';
        }
        $login = $salary->getUser()->getUsername();
        $nom = $salary->getNom();
        $prenom = $salary->getPrenom();
        $template = $this->perso->getEmailAfterActivation();
        $objet = $this->perso->getObjMailAfterActivation();
        $lienConnexion = $this->container->getParameter('url_site');
//        $email = $salary->getEmailPerso();
        $email = 'turki@intersa.fr';
        if (is_null($email)) {
//            $email = $salary->getEmailPro();
            $email = 'turki@intersa.fr';
        }
        $template = str_replace('{{nom}}', "$nom", $template);
        $template = str_replace('{{prenom}}', "$prenom", $template);
        $template = str_replace('{{logo}}', "$this->logo", $template);
        $template = str_replace('{{login}}', "$login", $template);
        $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        preg_match_all('/lien_connexion\[([^]]+)\]/', $template, $matches);


        foreach ($matches[1] as $linkName) {

            if (!empty($linkName)) {
                $lienConnexionHtml = '<a href="' . $lienConnexion . '">' . $linkName . '</a>';
                $template = str_replace("{{lien_connexion[$linkName]}}", "$lienConnexionHtml", $template);
            }
            $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        }

        $this->container->get('login.mailer')->sendMail($objet, $email, $template);
    }

    public function sendPasswordRecovery(Salary $salary, $lienChangementPassowrd) {
        $nom = $salary->getNom();
        $prenom = $salary->getPrenom();
        $login = $salary->getUser()->getUsername();
//        $email = $salary->getEmailPerso();
        $email = 'turki@intersa.fr';
        if (is_null($email)) {
//            $email = $salary->getEmailPro();
            $email = 'turki@intersa.fr';
        }

        $this->logo = '<img src="' . $this->container->getParameter('url_site') . '/uploads/' . $this->perso->getLogo() . '" width="60" />';
        $template = $this->perso->getEmailForgotPassword();
        $objet = $this->perso->getObjMailPwd();
        $template = str_replace('{{nom}}', "$nom", $template);
        $template = str_replace('{{prenom}}', "$prenom", $template);
        $template = str_replace('{{logo}}', "$this->logo", $template);
        $template = str_replace('{{login}}', "$login", $template);
        preg_match_all('/lien_changement_motdepasse\[([^]]+)\]/', $template, $matches);

        //Html of link Activation
        foreach ($matches[1] as $linkName) {

            if (!empty($linkName)) {
                $lienChangementPassowrdHtml = '<a href="' . $lienChangementPassowrd . '">' . $linkName . '</a>';
                $template = str_replace("{{lien_changement_motdepasse[$linkName]}}", "$lienChangementPassowrdHtml", $template);
            }
            $template = str_replace('{{lien_changement_motdepasse}}', "$lienChangementPassowrd", $template);
        }

        $this->container->get('login.mailer')->sendMail($objet, $email, $template);
    }

    public function getHtmlBulletin(Salary $salary, $mois, $annee) {
        $nom = $salary->getNom();
        $prenom = $salary->getPrenom();
        $login = $salary->getUser()->getUsername();
        $lienConnexion = $this->container->getParameter('url_site');
        $marray = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];
        $mois = $marray[$mois - 1];
        $objet = $this->perso->getObjMailBulletin();
        $template = $this->perso->getEmailBulletin();

//        $email = $salary->getEmailPerso(); 
        $email = 'turki@intersa.fr';
        if (is_null($email)) {
//            $email = $salary->getEmailPro(); 
            $email = 'turki@intersa.fr';
        }

        $template = str_replace('{{nom}}', "$nom", $template);
        $template = str_replace('{{prenom}}', "$prenom", $template);
        $template = str_replace('{{logo}}', "$this->logo", $template);
        $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        $template = str_replace('{{mois_fiche_paie}}', "$mois", $template);
        $template = str_replace('{{annee_fiche_paie}}', "$annee", $template);
        $template = str_replace('{{login}}', "$login", $template);
        preg_match_all('/lien_connexion\[([^]]+)\]/', $template, $matches);

        //Html of link Activation
        foreach ($matches[1] as $linkName) {

            if (!empty($linkName)) {
                $lienConnexionHtml = '<a href="' . $lienConnexion . '">' . $linkName . '</a>';
                $template = str_replace("{{lien_connexion[$linkName]}}", "$lienConnexionHtml", $template);
            }
            $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        }

//        var_dump($email);
//        die('getHtmlBulletin');
        $this->container->get('login.mailer')->sendMail($objet, $email, $template);
    }

    public function getHtmlDocSign(Salary $salary) {
        $nom = $salary->getNom();
        $prenom = $salary->getPrenom();
        $login = $salary->getUser()->getUsername();
        $lienConnexion = $this->container->getParameter('url_site');
        $objet = $this->perso->getObjMailDocSign();
        $template = $this->perso->getEmailDocSign();

//        $email = $salary->getEmailPerso();
        $email = 'turki@intersa.fr';
        if (is_null($email)) {
//            $email = $salary->getEmailPro();
            $email = 'turki@intersa.fr';
        }

        $template = str_replace('{{nom}}', "$nom", $template);
        $template = str_replace('{{prenom}}', "$prenom", $template);
        $template = str_replace('{{logo}}', "$this->logo", $template);
        $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        $template = str_replace('{{login}}', "$login", $template);
        preg_match_all('/lien_connexion\[([^]]+)\]/', $template, $matches);

        //Html of link Activation
        foreach ($matches[1] as $linkName) {

            if (!empty($linkName)) {
                $lienConnexionHtml = '<a href="' . $lienConnexion . '">' . $linkName . '</a>';
                $template = str_replace("{{lien_connexion[$linkName]}}", "$lienConnexionHtml", $template);
            }
            $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        }
        $this->container->get('login.mailer')->sendMail($objet, $email, $template);
    }

    public function getHtmlDoc(Salary $salary) {
        $nom = $salary->getNom();
        $prenom = $salary->getPrenom();
        $login = $salary->getUser()->getUsername();
        $lienConnexion = $this->container->getParameter('url_site');
        $objet = $this->perso->getObjMailDoc();
        $template = $this->perso->getEmailDoc();

//        $email = $salary->getEmailPerso();
        $email = 'turki@intersa.fr';
        if (is_null($email)) {
//            $email = $salary->getEmailPro();
            $email = 'turki@intersa.fr';
        }

        $template = str_replace('{{nom}}', "$nom", $template);
        $template = str_replace('{{prenom}}', "$prenom", $template);
        $template = str_replace('{{logo}}', "$this->logo", $template);
        $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        $template = str_replace('{{login}}', "$login", $template);
        preg_match_all('/lien_connexion\[([^]]+)\]/', $template, $matches);

        //Html of link Activation
        foreach ($matches[1] as $linkName) {

            if (!empty($linkName)) {
                $lienConnexionHtml = '<a href="' . $lienConnexion . '">' . $linkName . '</a>';
                $template = str_replace("{{lien_connexion[$linkName]}}", "$lienConnexionHtml", $template);
            }
            $template = str_replace('{{lien_connexion}}', "$lienConnexion", $template);
        }
        $this->container->get('login.mailer')->sendMail($objet, $email, $template);
    }

    public function sendPrintList($salaries, $month, $year, $list1p = array(), $list2p = array(), $filename = ' (à télécharger depuis la console) ') {

        $this->container->enterScope('request');
        $this->container->set('request', new Request(), 'request');
        $perso = $this->em->getRepository('BackBundle:Personnalisation')->find(1);
        $arr_emails = explode(',', $perso->getEmailsNotification());
        $company = $this->container->getParameter('client_name');

//        var_dump($list1p);
//         var_dump($list2p);
//        var_dump($filename);
//        die('$filename');
        $html = $this->container->get('templating')->render('BackBundle:Emails:send_report.html.twig', array(
            'salaries' => $salaries,
            'month' => $month,
            'year' => $year,
            'list1p' => $list1p,
            'list2p' => $list2p,
            'filename' => $filename,
            'company' => $company
        ));

        $objet = 'Liste des bulletins à imprimer';

        $email = $this->container->getParameter('email_manager');
//        var_dump($email);
//        die('sendPrintList');
        $this->container->get('login.mailer')->sendMailAdmins($objet, $arr_emails, $html);
    }

    public function sendPrintList2($salaries, $month, $year, $list1p = array(), $filename = ' (à télécharger depuis la console) ') {

        $this->container->enterScope('request');
        $this->container->set('request', new Request(), 'request');
        $perso = $this->em->getRepository('BackBundle:Personnalisation')->find(1);
        $arr_emails = explode(',', $perso->getEmailsNotification());
        $company = $this->container->getParameter('client_name');

//        var_dump($list1p);
//         var_dump($list2p);
//        var_dump($filename);
//        die('$filename');
        $html = $this->container->get('templating')->render('BackBundle:Emails:send_report2.html.twig', array(
            'salaries' => $salaries,
            'month' => $month,
            'year' => $year,
            'list1p' => $list1p,
            'filename' => $filename,
            'company' => $company
        ));

        $objet = 'Liste des bulletins à imprimer';

        $email = $this->container->getParameter('email_manager');
//        var_dump($email);
//        die('sendPrintList');
        $this->container->get('login.mailer')->sendMailAdmins($objet, $arr_emails, $html);
    }

    public function getHtmlBulletinObselet(Salary $salary, $month, $year) {
        $nom = $salary->getNom();
        $prenom = $salary->getPrenom();
//        $login = $salary->getUser()->getUsername();
//        $lienConnexion = $this->container->getParameter('url_site');
        $objet = $this->perso->getObjMaiLObseletDoc();
        $template = $this->perso->getEmailObseletDoc();

//        $email = $salary->getEmailPerso();
        $email = 'turki@intersa.fr';
        if (is_null($email)) {
//            $email = $salary->getEmailPro();
            $email = 'turki@intersa.fr';
        }

        $template = str_replace('{{nom}}', "$nom", $template);
        $template = str_replace('{{prenom}}', "$prenom", $template);
        $template = str_replace('{{logo}}', "$this->logo", $template);
        $template = str_replace('{{mois_fiche_paie}}', "$month", $template);
        $template = str_replace('{{annee_fiche_paie}}', "$year", $template);
//        preg_match_all('/lien_connexion\[([^]]+)\]/',$template, $matches);
        //Html of link Activation
        $this->container->get('login.mailer')->sendMail($objet, $email, $template);
    }

}
