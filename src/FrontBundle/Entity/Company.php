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
 * @ORM\Entity(repositoryClass="CompanyRepository")
 * @ORM\Table(name="company")
 * @ORM\HasLifecycleCallbacks

 */
class Company {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="FrontBundle\Entity\CoDoc", mappedBy="companies")
     */
    private $codocs;

    /**
     * @ORM\ManyToMany(targetEntity="FrontBundle\Entity\RhUser", inversedBy="companies")
     */
    private $rhusers;

    /**
     * Constructor
     */
    public function __construct() {
        $this->codocs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rhusers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString() {
        return (string) $this->getNom();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Company
     */
    public function setNom($nom) {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom() {
        return $this->nom;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Company
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Add codocs
     *
     * @param \FrontBundle\Entity\CoDoc $codocs
     * @return Company
     */
    public function addCodoc(\FrontBundle\Entity\CoDoc $codocs) {
        $this->codocs[] = $codocs;

        return $this;
    }

    /**
     * Remove codocs
     *
     * @param \FrontBundle\Entity\CoDoc $codocs
     */
    public function removeCodoc(\FrontBundle\Entity\CoDoc $codocs) {
        $this->codocs->removeElement($codocs);
    }

    /**
     * Get codocs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodocs() {
        return $this->codocs;
    }

    /**
     * Add rhusers
     *
     * @param \FrontBundle\Entity\RhUser $rhusers
     * @return Company
     */
    public function addRhuser(\FrontBundle\Entity\RhUser $rhusers) {
        $this->rhusers[] = $rhusers;

        return $this;
    }

    /**
     * Remove rhusers
     *
     * @param \FrontBundle\Entity\RhUser $rhusers
     */
    public function removeRhuser(\FrontBundle\Entity\RhUser $rhusers) {
        $this->rhusers->removeElement($rhusers);
    }

    /**
     * Get rhusers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRhusers() {
        return $this->rhusers;
    }

}
