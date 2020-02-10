<?php

namespace FrontBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FrontBundle\Entity\CoDocRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="co_doc")
 *
 */
class CoDoc {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(name="name", type="string" , length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="doc", type="string" , length=255)
     * @Assert\NotBlank(message="SVP, uploader un document.")
     * @Assert\File(
     * maxSize = "10M",
     * mimeTypes={ "application/pdf", "application/msword", "image/jpeg", "image/png", "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"})
     */
    private $doc;
    /**
     * @ORM\ManyToMany(targetEntity="FrontBundle\Entity\Company", inversedBy="codocs")
     *
     */
    private $companies;
    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="visibility", type="boolean")
     */
    private $visibility;

    /**
     * @ORM\Column(name="to_sign", type="boolean")
     */
    private $toSign;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->companies = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return CoDoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set doc
     *
     * @param string $doc
     * @return CoDoc
     */
    public function setDoc($doc)
    {
        $this->doc = $doc;

        return $this;
    }

    /**
     * Get doc
     *
     * @return string 
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return CoDoc
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
     * Set visibility
     *
     * @param boolean $visibility
     * @return CoDoc
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get visibility
     *
     * @return boolean 
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set toSign
     *
     * @param boolean $toSign
     * @return CoDoc
     */
    public function setToSign($toSign)
    {
        $this->toSign = $toSign;

        return $this;
    }

    /**
     * Get toSign
     *
     * @return boolean 
     */
    public function getToSign()
    {
        return $this->toSign;
    }

    /**
     * Add companies
     *
     * @param \FrontBundle\Entity\Company $companies
     * @return CoDoc
     */
    public function addCompany(\FrontBundle\Entity\Company $companies)
    {
        $this->companies[] = $companies;

        return $this;
    }

    /**
     * Remove companies
     *
     * @param \FrontBundle\Entity\Company $companies
     */
    public function removeCompany(\FrontBundle\Entity\Company $companies)
    {
        $this->companies->removeElement($companies);
    }

    /**
     * Get companies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompanies()
    {
        return $this->companies;
    }
}
