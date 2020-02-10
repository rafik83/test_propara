<?php

namespace FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FrontBundle\Entity\DocumentRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="document")
 *
 */
class Document {

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
     * mimeTypes={ "application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"})
     */
    private $doc;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Salary", cascade={"persist"})
     * @ORM\JoinColumn(name="salary_id", nullable=false)
     */
    private $salary;

    /**
     * @ORM\Column(name="visibility", type="boolean")
     */
    private $visibility;
    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", nullable=false)
     */
    private $category;
    /**
     * @ORM\Column(name="special_doc", type="boolean", nullable=true)
     */
    private $specialDoc;
    /**
     * @ORM\Column(name="special_signed", type="boolean", nullable=true)
     */
    private $specialSigned;
    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->specialSigned = false;

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
     * @return Document
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Document
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
     * @return Document
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
     * Set salary
     *
     * @param \FrontBundle\Entity\Salary $salary
     * @return Document
     */
    public function setSalary(\FrontBundle\Entity\Salary $salary)
    {
        $this->salary = $salary;

        return $this;
    }

    /**
     * Get salary
     *
     * @return \FrontBundle\Entity\Salary 
     */
    public function getSalary()
    {
        return $this->salary;
    }

    /**
     * Set category
     *
     * @param \FrontBundle\Entity\Category $category
     * @return Document
     */
    public function setCategory(\FrontBundle\Entity\Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \FrontBundle\Entity\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set doc
     *
     * @param string $doc
     * @return Document
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
     * Set specialDoc
     *
     * @param boolean $specialDoc
     * @return Document
     */
    public function setSpecialDoc($specialDoc)
    {
        $this->specialDoc = $specialDoc;

        return $this;
    }

    /**
     * Get specialDoc
     *
     * @return boolean 
     */
    public function getSpecialDoc()
    {
        return $this->specialDoc;
    }

    /**
     * Set specialSigned
     *
     * @param boolean $specialSigned
     * @return Document
     */
    public function setSpecialSigned($specialSigned)
    {
        $this->specialSigned = $specialSigned;

        return $this;
    }

    /**
     * Get specialSigned
     *
     * @return boolean 
     */
    public function getSpecialSigned()
    {
        return $this->specialSigned;
    }
}
