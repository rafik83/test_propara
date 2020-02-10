<?php
namespace BackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="BuRepository")
 * @ORM\Table(name="bulletin_unite")
 */
class Bu {


    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(name="month", type="string")
     */
    private $month;
    /**
     * @ORM\Column(name="year", type="string")
     */
    private $year;
    /**
     * @ORM\Column(name="bulletin", type="string", length=255,nullable=false)
     * @Assert\File(mimeTypes={ "application/pdf"})
     */
    private $bulletin;
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
     * @ORM\Column(name="ext", type="string" , nullable = true)
     */
    private $ext;
    /**
     * @ORM\Column(name="comment_error", type="text" , nullable = true)
     */
    private $commentError;
    /**
     * @ORM\Column(name="in_progress", type="boolean")
     */
    private $inProgress;
    /**
     * @ORM\ManyToOne(targetEntity="FrontBundle\Entity\Salary", cascade={"persist"})
     * @ORM\JoinColumn(name="salary_id", nullable=false)
     */
    private $salary;
    /**
     * @ORM\Column(name="disabled", type="boolean")
     */
    private $disabled;

    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->signed = 0;
        $this->inProgress = 0;
        $this->disabled = 0;
        $this->error = 0;
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
     * Set month
     *
     * @param string $month
     * @return Bu
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return string 
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set year
     *
     * @param string $year
     * @return Bu
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return string 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set bulletin
     *
     * @param string $bulletin
     * @return Bu
     */
    public function setBulletin($bulletin)
    {
        $this->bulletin = $bulletin;

        return $this;
    }

    /**
     * Get bulletin
     *
     * @return string 
     */
    public function getBulletin()
    {
        return $this->bulletin;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Bu
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
     * @return Bu
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
     * Set error
     *
     * @param boolean $error
     * @return Bu
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
     * @return Bu
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
     * Set inProgress
     *
     * @param boolean $inProgress
     * @return Bu
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
     * Set salary
     *
     * @param \FrontBundle\Entity\Salary $salary
     * @return Bu
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
     * Set disabled
     *
     * @param boolean $disabled
     * @return Bu
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
     * Set ext
     *
     * @param string $ext
     * @return Bu
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
}
