<?php

/*
 * This file is part of the fulldon project
 *
 * (c) SAMI BOUSSACSOU <boussacsou@intersa.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SalaryRepository")
 * @ORM\Table(name="salary")
 * @ORM\HasLifecycleCallbacks

 */
class Salary {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * 
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(name="prenom", type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @ORM\Column(name="poste", type="string", length=255, nullable=true)
     */
    private $poste;

    /**
     * @ORM\Column(name="nature_contrat", type="string", length=255, nullable=true)
     */
    private $natureContrat;

    /**
     * @ORM\ManyToOne(targetEntity="Company", cascade={"persist"})
     * @ORM\JoinColumn(name="company_id", nullable=true)
     */
    private $company;

    /**
     * @ORM\Column(name="email_perso", type="string", length=255, nullable=true)
     */
    private $emailPerso;

    /**
     * @ORM\Column(name="email_pro", type="string", length=255, nullable=true)
     */
    private $emailPro;

    /**
     * @ORM\Column(name="telephone_perso", type="text",  nullable=true)
     */
    private $telephonePerso;

    /**
     * @ORM\Column(name="telephone_pro", type="text",  nullable=true)
     */
    private $telephonePro;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist", "remove"})
     * @ORM\JoinColumn( name="user_id", nullable=false);
     */
    private $user;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="birth_date", type="date",  nullable=true)
     */
    private $birthDay;

    /**
     * @ORM\Column(name="date_begin", type="date",  nullable=true)
     */
    private $dateBegin;

    /**
     * @ORM\Column(name="date_end", type="date",  nullable=true)
     */
    private $dateEnd;

    /**
     * @ORM\Column(name="matricule", type="string")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $matricule;

    /**
     * @ORM\Column(name="num_secu", type="string", length=255, nullable=true)
     */
    private $numSecu;

    /**
     * @ORM\Column(name="photo", type="string", length=255, nullable=true)
     * @Assert\File(
     * mimeTypes={ "image/jpeg", "image/png" }
     * )
     */
    private $photo;

    /**
     * @ORM\Column(name="is_docs", type="boolean",  nullable=true)
     */
    private $isDoc;

    /**
     * @ORM\Column(name="is_paper", type="boolean",  nullable=true)
     */
    private $isPaper;

    /**
     * @ORM\Column(name="activation_sended", type="boolean",  nullable=true)
     */
    private $activationSended;

    /**
     * @ORM\Column(name="adresse", type="text",  nullable=true)
     */
    private $adresse;

    /**
     * @ORM\Column(name="ville", type="string", length=255,  nullable=true)
     */
    private $ville;

    /**
     * @ORM\Column(name="zipcode", type="string", length=255, nullable=true)
     */
    private $zipcode;

    public function __construct() {
        $this->createdAt = new \Datetime();
    }

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
     * Set nom
     *
     * @param string $nom
     * @return Salary
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     * @return Salary
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set poste
     *
     * @param string $poste
     * @return Salary
     */
    public function setPoste($poste)
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * Get poste
     *
     * @return string 
     */
    public function getPoste()
    {
        return $this->poste;
    }

    /**
     * Set natureContrat
     *
     * @param string $natureContrat
     * @return Salary
     */
    public function setNatureContrat($natureContrat)
    {
        $this->natureContrat = $natureContrat;

        return $this;
    }

    /**
     * Get natureContrat
     *
     * @return string 
     */
    public function getNatureContrat()
    {
        return $this->natureContrat;
    }

    /**
     * Set emailPerso
     *
     * @param string $emailPerso
     * @return Salary
     */
    public function setEmailPerso($emailPerso)
    {
        $this->emailPerso = $emailPerso;

        return $this;
    }

    /**
     * Get emailPerso
     *
     * @return string 
     */
    public function getEmailPerso()
    {
        return $this->emailPerso;
    }

    /**
     * Set emailPro
     *
     * @param string $emailPro
     * @return Salary
     */
    public function setEmailPro($emailPro)
    {
        $this->emailPro = $emailPro;

        return $this;
    }

    /**
     * Get emailPro
     *
     * @return string 
     */
    public function getEmailPro()
    {
        return $this->emailPro;
    }

    /**
     * Set telephonePerso
     *
     * @param string $telephonePerso
     * @return Salary
     */
    public function setTelephonePerso($telephonePerso)
    {
        $this->telephonePerso = $telephonePerso;

        return $this;
    }

    /**
     * Get telephonePerso
     *
     * @return string 
     */
    public function getTelephonePerso()
    {
        return $this->telephonePerso;
    }

    /**
     * Set telephonePro
     *
     * @param string $telephonePro
     * @return Salary
     */
    public function setTelephonePro($telephonePro)
    {
        $this->telephonePro = $telephonePro;

        return $this;
    }

    /**
     * Get telephonePro
     *
     * @return string 
     */
    public function getTelephonePro()
    {
        return $this->telephonePro;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Salary
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set birthDay
     *
     * @param \DateTime $birthDay
     * @return Salary
     */
    public function setBirthDay($birthDay)
    {
        $this->birthDay = $birthDay;

        return $this;
    }

    /**
     * Get birthDay
     *
     * @return \DateTime 
     */
    public function getBirthDay()
    {
        return $this->birthDay;
    }

    /**
     * Set dateBegin
     *
     * @param \DateTime $dateBegin
     * @return Salary
     */
    public function setDateBegin($dateBegin)
    {
        $this->dateBegin = $dateBegin;

        return $this;
    }

    /**
     * Get dateBegin
     *
     * @return \DateTime 
     */
    public function getDateBegin()
    {
        return $this->dateBegin;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     * @return Salary
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime 
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set matricule
     *
     * @param string $matricule
     * @return Salary
     */
    public function setMatricule($matricule)
    {
        $this->matricule = $matricule;

        return $this;
    }

    /**
     * Get matricule
     *
     * @return string 
     */
    public function getMatricule()
    {
        return $this->matricule;
    }

    /**
     * Set numSecu
     *
     * @param string $numSecu
     * @return Salary
     */
    public function setNumSecu($numSecu)
    {
        $this->numSecu = $numSecu;

        return $this;
    }

    /**
     * Get numSecu
     *
     * @return string 
     */
    public function getNumSecu()
    {
        return $this->numSecu;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return Salary
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set isDoc
     *
     * @param boolean $isDoc
     * @return Salary
     */
    public function setIsDoc($isDoc)
    {
        $this->isDoc = $isDoc;

        return $this;
    }

    /**
     * Get isDoc
     *
     * @return boolean 
     */
    public function getIsDoc()
    {
        return $this->isDoc;
    }

    /**
     * Set isPaper
     *
     * @param boolean $isPaper
     * @return Salary
     */
    public function setIsPaper($isPaper)
    {
        $this->isPaper = $isPaper;

        return $this;
    }

    /**
     * Get isPaper
     *
     * @return boolean 
     */
    public function getIsPaper()
    {
        return $this->isPaper;
    }

    /**
     * Set activationSended
     *
     * @param boolean $activationSended
     * @return Salary
     */
    public function setActivationSended($activationSended)
    {
        $this->activationSended = $activationSended;

        return $this;
    }

    /**
     * Get activationSended
     *
     * @return boolean 
     */
    public function getActivationSended()
    {
        return $this->activationSended;
    }
    
     public function getFullName() {
        return 'Matricule #: ' . $this->matricule . ' Nom complet : ' . $this->nom . ' ' . $this->prenom;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return Salary
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return Salary
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return Salary
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string 
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set company
     *
     * @param \FrontBundle\Entity\Company $company
     * @return Salary
     */
    public function setCompany(\FrontBundle\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \FrontBundle\Entity\Company 
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set user
     *
     * @param \FrontBundle\Entity\User $user
     * @return Salary
     */
    public function setUser(\FrontBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \FrontBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
