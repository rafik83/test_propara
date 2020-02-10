<?php
namespace BackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="ZipFileRepository")
 * @ORM\Table(name="zip_file")
 * @UniqueEntity("name")
 *
 */
class ZipFile {


    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(name="name", type="string")
     */
    private $name;
    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    /**
     * @ORM\Column(name="signed", type="boolean" )
     */
    private $signed;
    /**
     * @ORM\Column(name="error", type="boolean" , nullable = true)
     */
    private $error;
    /**
     * @ORM\Column(name="comment_error", type="text" , nullable = true)
     */
    private $commentError;

    /**
     * @ORM\Column(name="in_progress", type="boolean")
     */
    private $inProgress;
    /**
     * @ORM\Column(name="disabled", type="boolean")
     */
    private $disabled;
    /**
     * @ORM\ManyToOne(targetEntity="FrontBundle\Entity\Company", cascade={"persist"})
     * @ORM\JoinColumn(name="company_id", nullable=true)
     */
    private $company;


    public function __construct()
    {

        $this->createdAt = new \Datetime();
        $this->inProgress = false;
        $this->signed = false;
        $this->error = false;
        $this->disabled = false;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ZipFile
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
     * @return ZipFile
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
     * Set signed
     *
     * @param boolean $signed
     * @return ZipFile
     */
    public function setSigned($signed)
    {
        $this->signed = $signed;

        return $this;
    }

    /**
     * Get signed
     *
     * @return boolean 
     */
    public function getSigned()
    {
        return $this->signed;
    }



    /**
     * Set inProgress
     *
     * @param boolean $inProgress
     * @return ZipFile
     */
    public function setInProgress($inProgress)
    {
        $this->inProgress = $inProgress;

        return $this;
    }

    /**
     * Get inProgress
     *
     * @return boolean 
     */
    public function getInProgress()
    {
        return $this->inProgress;
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
     * Set error
     *
     * @param boolean $error
     * @return ZipFile
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return boolean 
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set commentError
     *
     * @param string $commentError
     * @return ZipFile
     */
    public function setCommentError($commentError)
    {
        $this->commentError = $commentError;

        return $this;
    }

    /**
     * Get commentError
     *
     * @return string 
     */
    public function getCommentError()
    {
        return $this->commentError;
    }

    /**
     * Set disabled
     *
     * @param boolean $disabled
     * @return ZipFile
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Get disabled
     *
     * @return boolean 
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set company
     *
     * @param \FrontBundle\Entity\Company $company
     * @return ZipFile
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
}
