<?php

// src/BackBundle/DataFixtures/ORM/LoadUserData.php

namespace BackBunlde\DataFixtures\ORM;

use BackBundle\Entity\Personnalisation;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FrontBundle\Entity\RhUser;
use FrontBundle\Entity\Role;
use FrontBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;

class LoadUserData extends ContainerAware implements FixtureInterface {

    public function load(ObjectManager $manager) {

        $role = new Role();
        $role->setName('ADMIN');
        $role->setRole('ROLE_ADMIN');
        $role->setDesc('');
        $manager->persist($role);
        $manager->flush();

        $role = new Role();
        $role->setName('RH');
        $role->setRole('ROLE_RH');
        $role->setDesc('');
        $manager->persist($role);
        $manager->flush();
        
        
        $role = new Role();
        $role->setName('RH_WRITE');
        $role->setRole('ROLE_RH_LIMITE');
        $role->setDesc('');
        $manager->persist($role);
        $manager->flush();
        

        $role = new Role();
        $role->setName('SALARY');
        $role->setRole('ROLE_USER');
        $role->setDesc('');
        $manager->persist($role);
        $manager->flush();


        $user = new User();
        $userRh = new RhUser();
        $user->setUsername('admin');
        $plainPassword = $this->generatePassword(8);
        var_dump('pwd');
        var_dump($plainPassword);//WtdBfod2
        $user->setIsActive(true);
        $user->setSalt(uniqid(mt_rand())); // Unique salt for user
        $user->setIsActive(true);
        $user->addRole($manager->getRepository('FrontBundle:Role')->findOneBy(array('role' => 'ROLE_ADMIN')));
        // Set encrypted password
        $encoder = $this->container->get('security.encoder_factory')
                ->getEncoder($user);
        $password = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($password);
        $userRh->setEmail('turki@intersa.fr');
        $userRh->setNom('admin');
        $userRh->setPrenom('admin');
        $userRh->setUser($user);

        $manager->persist($userRh);
        $manager->flush();

        $personnalisation = new Personnalisation();
        $personnalisation->setObjMailActivation('Activation de votre compte FullOffice RH');
        $personnalisation->setObjMailAfterActivation('Votre compte FullOffice RH est activé');
        $personnalisation->setObjMailBulletin('Votre Bulletin de paie');
        $personnalisation->setObjMailDoc('Nouveau document');
        $personnalisation->setObjMailDocSign('Un document en attente de signature');
        $personnalisation->setObjMailPwd('Réinitialisation de votre mot de passe FullOffice RH');
        $personnalisation->setObjMaiLObseletDoc('Modification document');

        $personnalisation->setEmailActivation('<p style="line-height: 20.8px;"><span style="line-height: 20.8px;">{{logo}}</span></p>

<p style="line-height: 20.8px;">&nbsp;</p>

<p style="line-height: 20.8px;">Bonjour&nbsp;{{nom}} {{prenom}},</p>

<p style="line-height: 20.8px;">Logo :&nbsp;{{login}}</p>

<p style="line-height: 20.8px;">Afin d&#39;activer votre compte veuillez&nbsp;{{lien_activation[Cliquez Ici]}}</p>

<p style="line-height: 20.8px;">Bien cordialement,</p>

<p style="line-height: 20.8px;">La direction</p>

');
        $personnalisation->setEmailAfterActivation('
<p style="line-height: 20.8px;"><span style="line-height: 20.8px;">{{logo}}</span></p>

<p style="line-height: 20.8px;">&nbsp;</p>

<p style="line-height: 20.8px;">Bonjour&nbsp;{{nom}} {{prenom}},</p>

<p style="line-height: 20.8px;">Login :&nbsp;{{login}}</p>

<p style="line-height: 20.8px;">Votre compte RH est d&eacute;sormais activ&eacute;, &nbsp;<span style="line-height: 20.7999992370605px;">&nbsp;{{lien_connexion[Cliquez Ici]}}&nbsp;pour acc&eacute;der &agrave; votre espace.&nbsp;</span></p>

<p style="line-height: 20.8px;">Bien cordialement,</p>

<p style="line-height: 20.8px;">La direction</p>

        ');
        $personnalisation->setEmailBulletin('
        <p style="line-height: 20.8px;">{{logo}}</p>

<p style="line-height: 20.8px;">Bonjour {{nom}} ,</p>

<p style="line-height: 20.8px;">Votre bulletin de paie de&nbsp;{{mois_fiche_paie}} {{annee_fiche_paie}} est d&eacute;sormais&nbsp;disponible sur votre espace RH. Pour le t&eacute;l&eacute;charger, veuillez&nbsp;{{lien_connexion[Cliquez Ici]}}&nbsp;pour acc&eacute;der &agrave; votre espace.&nbsp;</p>

<p style="line-height: 20.8px;">Tr&egrave;s cordialement</p>

        ');
        $personnalisation->setEmailDoc('<p style="line-height: 20.8px;">{{logo}}</p>

<p style="line-height: 20.8px;">Bonjour {{nom}} {{prenom}},</p>

<p style="line-height: 20.8px;">Un nouveau document est disponible sur votre espace RH.</p>

<p style="line-height: 20.8px;">Pour le t&eacute;l&eacute;charger, veuillez&nbsp;{{lien_connexion[Cliquez Ici]}}&nbsp;pour acc&eacute;der &agrave; votre espace.&nbsp;</p>

<p style="line-height: 20.8px;">Tr&egrave;s cordialement,</p>

<p style="line-height: 20.8px;">La direction</p>');
        $personnalisation->setEmailDocSign('
        <p style="line-height: 20.8px;">{{logo}}</p>

<p style="line-height: 20.8px;">Bonjour {{nom}} {{prenom}},</p>

<p style="line-height: 20.8px;">Un nouveau document est disponible sur votre espace RH.</p>

<p style="line-height: 20.8px;">Pour le signer, veuillez&nbsp;{{lien_connexion[Cliquez Ici]}}&nbsp;pour acc&eacute;der &agrave; votre espace.&nbsp;</p>

<p style="line-height: 20.8px;">Tr&egrave;s cordialement,</p>

<p style="line-height: 20.8px;">La direction</p>
');


        $personnalisation->setEmailObseletDoc('
        <p style="line-height: 20.8px;">{{logo}}</p>

<p style="line-height: 20.8px;">Bonjour {{nom}} ,</p>

<p style="line-height: 20.8px;">Votre bulletin de paie de&nbsp;{{mois_fiche_paie}} {{annee_fiche_paie}} est d&eacute;sormais&nbsp;modifier.Nous vous invitons à vous connecter à votre compte en ligne afin de telecharger sa derniere version mise à jour.&nbsp;</p>

<p style="line-height: 20.8px;">Tr&egrave;s cordialement</p>

        ');
        $personnalisation->setEmailForgotPassword('
        <p style="line-height: 20.8px;"><span style="line-height: 20.8px;">{{logo}}</span></p>

<p style="line-height: 20.8px;">Bonjour&nbsp;{{nom}},</p>

<p style="line-height: 20.8px;">Afin de pouvoir r&eacute;initiailiser votre mot de passe, veuillez&nbsp;{{lien_changement_motdepasse[Cliquez Ici]}}</p>

<p style="line-height: 20.8px;">Tr&egrave;s cordialement</p>

        ');


        $manager->persist($personnalisation);
        $manager->flush();


        $this->container->get('login.mailer')->
                sendMail('Passowrd for client : ' . $this->container->getParameter('client_id'), 'turki@intersa.fr', 'Password : ' . $plainPassword);
    }

    function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

}
