<?php
/*
 * This file is part of the fulldon project
 *
 * (c) SAMI BOUSSACSOU <boussacsou@intersa.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Table(name="personnalisation")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="BackBundle\Entity\PersonnalisationRepository")
 */
class Personnalisation
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(name="couleur", type="string", length=100, nullable=true)
     */
    private $couleur;
    /**
     * @ORM\Column(name="logo", type="string", length=255,nullable=true)
     * @Assert\File(mimeTypes={ "image/png", "image/jpeg"})
     */
    private $logo;
    /**
     * @ORM\Column(name="obj_mail_activation", type="string",  nullable=true)
     */
    private $objMailActivation;
    /**
     * @ORM\Column(name="email_activation", type="text", nullable=true)
     */
    private $emailActivation;
    /**
     * @ORM\Column(name="obj_mail_after_activation", type="string",  nullable=true)
     */
    private $objMailAfterActivation;
    /**
     * @ORM\Column(name="email_after_activation", type="text", nullable=true)
     */
    private $emailAfterActivation;
    /**
     * @ORM\Column(name="obj_mail_pwd", type="string",  nullable=true)
     */
    private $objMailPwd;
    /**
     * @ORM\Column(name="email_forgot_password", type="text", nullable=true)
     */
    private $emailForgotPassword;
    /**
     * @ORM\Column(name="obj_mail_bulletin", type="string",  nullable=true)
     */
    private $objMailBulletin;
    /**
     * @ORM\Column(name="email_bulletin", type="text", nullable=true)
     */
    private $emailBulletin;
    /**
     * @ORM\Column(name="obj_mail_docsign", type="string",  nullable=true)
     */
    private $objMailDocSign;
    /**
     * @ORM\Column(name="email_docsign", type="text", nullable=true)
     */
    private $emailDocSign;
    /**
     * @ORM\Column(name="obj_mail_doc", type="string",  nullable=true)
     */
    private $objMailDoc;
    /**
     * @ORM\Column(name="email_doc", type="text", nullable=true)
     */
    private $emailDoc;
    /**
     * @ORM\Column(name="emails_notification", type="text", nullable=true)
     */
    private $emailsNotification;
    /**
     * @ORM\Column(name="obj_email_relance", type="text", nullable=true)
     */
    private $objMailRelance;
    /**
     * @ORM\Column(name="email_relance", type="text", nullable=true)
     */
    private $emailRelance;
    /**
     * @ORM\Column(name="obj_email_obseletdoc", type="text", nullable=true)
     */
     private $objMaiLObseletDoc;
    /**
     * @ORM\Column(name="email_obseletdoc", type="text", nullable=true)
     */
    private $emailObseletDoc;
    
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set couleur
     *
     * @param string $couleur
     * @return Personnalisation
     */
    public function setCouleur($couleur)
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * Get couleur
     *
     * @return string 
     */
    public function getCouleur()
    {
        return $this->couleur;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return Personnalisation
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set emailActivation
     *
     * @param string $emailActivation
     * @return Personnalisation
     */
    public function setEmailActivation($emailActivation)
    {
        $this->emailActivation = $emailActivation;

        return $this;
    }

    /**
     * Get emailActivation
     *
     * @return string 
     */
    public function getEmailActivation()
    {
        return $this->emailActivation;
    }

    /**
     * Set emailForgotPassword
     *
     * @param string $emailForgotPassword
     * @return Personnalisation
     */
    public function setEmailForgotPassword($emailForgotPassword)
    {
        $this->emailForgotPassword = $emailForgotPassword;

        return $this;
    }

    /**
     * Get emailForgotPassword
     *
     * @return string 
     */
    public function getEmailForgotPassword()
    {
        return $this->emailForgotPassword;
    }

    /**
     * Set emailBulletin
     *
     * @param string $emailBulletin
     * @return Personnalisation
     */
    public function setEmailBulletin($emailBulletin)
    {
        $this->emailBulletin = $emailBulletin;

        return $this;
    }

    /**
     * Get emailBulletin
     *
     * @return string 
     */
    public function getEmailBulletin()
    {
        return $this->emailBulletin;
    }

    /**
     * Set objMailActivation
     *
     * @param string $objMailActivation
     * @return Personnalisation
     */
    public function setObjMailActivation($objMailActivation)
    {
        $this->objMailActivation = $objMailActivation;

        return $this;
    }

    /**
     * Get objMailActivation
     *
     * @return string 
     */
    public function getObjMailActivation()
    {
        return $this->objMailActivation;
    }

    /**
     * Set objMailPwd
     *
     * @param string $objMailPwd
     * @return Personnalisation
     */
    public function setObjMailPwd($objMailPwd)
    {
        $this->objMailPwd = $objMailPwd;

        return $this;
    }

    /**
     * Get objMailPwd
     *
     * @return string 
     */
    public function getObjMailPwd()
    {
        return $this->objMailPwd;
    }

    /**
     * Set objMailBulletin
     *
     * @param string $objMailBulletin
     * @return Personnalisation
     */
    public function setObjMailBulletin($objMailBulletin)
    {
        $this->objMailBulletin = $objMailBulletin;

        return $this;
    }

    /**
     * Get objMailBulletin
     *
     * @return string 
     */
    public function getObjMailBulletin()
    {
        return $this->objMailBulletin;
    }

    /**
     * Set objMailDocSign
     *
     * @param string $objMailDocSign
     * @return Personnalisation
     */
    public function setObjMailDocSign($objMailDocSign)
    {
        $this->objMailDocSign = $objMailDocSign;

        return $this;
    }

    /**
     * Get objMailDocSign
     *
     * @return string 
     */
    public function getObjMailDocSign()
    {
        return $this->objMailDocSign;
    }

    /**
     * Set emailDocSign
     *
     * @param string $emailDocSign
     * @return Personnalisation
     */
    public function setEmailDocSign($emailDocSign)
    {
        $this->emailDocSign = $emailDocSign;

        return $this;
    }

    /**
     * Get emailDocSign
     *
     * @return string 
     */
    public function getEmailDocSign()
    {
        return $this->emailDocSign;
    }

    /**
     * Set objMailDoc
     *
     * @param string $objMailDoc
     * @return Personnalisation
     */
    public function setObjMailDoc($objMailDoc)
    {
        $this->objMailDoc = $objMailDoc;

        return $this;
    }

    /**
     * Get objMailDoc
     *
     * @return string 
     */
    public function getObjMailDoc()
    {
        return $this->objMailDoc;
    }

    /**
     * Set emailDoc
     *
     * @param string $emailDoc
     * @return Personnalisation
     */
    public function setEmailDoc($emailDoc)
    {
        $this->emailDoc = $emailDoc;

        return $this;
    }

    /**
     * Get emailDoc
     *
     * @return string 
     */
    public function getEmailDoc()
    {
        return $this->emailDoc;
    }

    /**
     * Set objMailAfterActivation
     *
     * @param string $objMailAfterActivation
     * @return Personnalisation
     */
    public function setObjMailAfterActivation($objMailAfterActivation)
    {
        $this->objMailAfterActivation = $objMailAfterActivation;

        return $this;
    }

    /**
     * Get objMailAfterActivation
     *
     * @return string 
     */
    public function getObjMailAfterActivation()
    {
        return $this->objMailAfterActivation;
    }

    /**
     * Set emailAfterActivation
     *
     * @param string $emailAfterActivation
     * @return Personnalisation
     */
    public function setEmailAfterActivation($emailAfterActivation)
    {
        $this->emailAfterActivation = $emailAfterActivation;

        return $this;
    }

    /**
     * Get emailAfterActivation
     *
     * @return string 
     */
    public function getEmailAfterActivation()
    {
        return $this->emailAfterActivation;
    }

    /**
     * Set emailsNotification
     *
     * @param string $emailsNotification
     * @return Personnalisation
     */
    public function setEmailsNotification($emailsNotification)
    {
        $this->emailsNotification = $emailsNotification;

        return $this;
    }

    /**
     * Get emailsNotification
     *
     * @return string 
     */
    public function getEmailsNotification()
    {
        return $this->emailsNotification;
    }

    /**
     * Set emailRelance
     *
     * @param string $emailRelance
     * @return Personnalisation
     */
    public function setEmailRelance($emailRelance)
    {
        $this->emailRelance = $emailRelance;

        return $this;
    }

    /**
     * Get emailRelance
     *
     * @return string 
     */
    public function getEmailRelance()
    {
        return $this->emailRelance;
    }

    /**
     * Set objMailRelance
     *
     * @param string $objMailRelance
     * @return Personnalisation
     */
    public function setObjMailRelance($objMailRelance)
    {
        $this->objMailRelance = $objMailRelance;

        return $this;
    }

    /**
     * Get objMailRelance
     *
     * @return string 
     */
    public function getObjMailRelance()
    {
        return $this->objMailRelance;
    }

    /**
     * Set objMaiLObseletDoc
     *
     * @param string $objMaiLObseletDoc
     * @return Personnalisation
     */
    public function setObjMaiLObseletDoc($objMaiLObseletDoc)
    {
        $this->objMaiLObseletDoc = $objMaiLObseletDoc;

        return $this;
    }

    /**
     * Get objMaiLObseletDoc
     *
     * @return string 
     */
    public function getObjMaiLObseletDoc()
    {
        return $this->objMaiLObseletDoc;
    }

    /**
     * Set emailObseletDoc
     *
     * @param string $emailObseletDoc
     * @return Personnalisation
     */
    public function setEmailObseletDoc($emailObseletDoc)
    {
        $this->emailObseletDoc = $emailObseletDoc;

        return $this;
    }

    /**
     * Get emailObseletDoc
     *
     * @return string 
     */
    public function getEmailObseletDoc()
    {
        return $this->emailObseletDoc;
    }
}
