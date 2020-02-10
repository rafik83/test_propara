<?php
namespace FrontBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo ;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="SignedDocRepository")
 * @ORM\Table(name="signed_doc")
 */
class SignedDoc {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="record", type="string", length=255)
     */
    private $record;
    /**
     * @ORM\Column(name="signature", type="string", length=255)
     */
    private $signature;
    /**
     * @ORM\Column(name="doc", type="string", length=255)
     */
    private $doc;
    /**
     * @ORM\Column(name="ext", type="string", length=255, nullable=true)
     */
    private $ext;
    /**
     * @ORM\ManyToOne(targetEntity="Salary", cascade={"persist"})
     * @ORM\JoinColumn(name="salary_id", nullable=false)
     */
    private $salary;
    /**
     * @ORM\Column(name="month", type="integer")
     */
    private $month;
    /**
     * @ORM\Column(name="year", type="integer")
     */
    private $year;
    /**
     * @ORM\Column(name="size", type="integer")
     */
    private $size;
    /**
     * @ORM\Column(name="obsolete", type="boolean", nullable=true)
     */
    private $obsolete;
    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    
    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime", nullable =true)
     */
    private $updated;
    
    
    /**
     * @Gedmo\Timestampable(on="change", field={"obsolete"})
     * @ORM\Column(name="doc_changed", type="datetime", nullable =true)
     */
    private $docChange;

    public function __construct()
    {
        $this->obsolete = 0;
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
     * Set record
     *
     * @param string $record
     * @return Bulletin
     */
    public function setRecord($record)
    {
        $this->record = $record;

        return $this;
    }

    /**
     * Get record
     *
     * @return string 
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * Set signature
     *
     * @param string $signature
     * @return Bulletin
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string 
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set doc
     *
     * @param string $doc
     * @return Bulletin
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
     * Set month
     *
     * @param integer $month
     * @return Bulletin
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return integer 
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return Bulletin
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set salary
     *
     * @param \FrontBundle\Entity\Salary $salary
     * @return Bulletin
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
     * Set size
     *
     * @param integer $size
     * @return SignedDoc
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set obsolete
     *
     * @param boolean $obsolete
     * @return SignedDoc
     */
    public function setObsolete($obsolete)
    {
        $this->obsolete = $obsolete;

        return $this;
    }

    /**
     * Get obsolete
     *
     * @return boolean 
     */
    public function getObsolete()
    {
        return $this->obsolete;
    }

    /**
     * Set ext
     *
     * @param string $ext
     * @return SignedDoc
     */
    public function setExt($ext)
    {
        $this->ext = $ext;

        return $this;
    }

    /**
     * Get ext
     *
     * @return string 
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return SignedDoc
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
     * Set updated
     *
     * @param \DateTime $updated
     * @return SignedDoc
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set docChange
     *
     * @param \DateTime $docChange
     * @return SignedDoc
     */
    public function setDocChange($docChange)
    {
        $this->docChange = $docChange;

        return $this;
    }

    /**
     * Get docChange
     *
     * @return \DateTime 
     */
    public function getDocChange()
    {
        return $this->docChange;
    }
}
