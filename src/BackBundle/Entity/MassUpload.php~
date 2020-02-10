<?php

namespace BackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="MassUploadRepository")
 * @ORM\Table(name="mass_upload")
 *
 */
class MassUpload
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="uid", type="string")
     */
    private $uidUpload;
    /**
     * @ORM\Column(name="month", type="string")
     */
    private $month;
    /**
     * @ORM\Column(name="year", type="string")
     */
    private $year;
    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    /**
     * @ORM\Column(name="verified", type="boolean" )
     */
    private $verified;
    /**
     * @ORM\ManyToOne(targetEntity="FrontBundle\Entity\Company", cascade={"persist"})
     * @ORM\JoinColumn(name="company_id", nullable=true)
     */
    private $company;
    /**
     * @ORM\Column(name="nb_bulletins", type="string", nullable=true)
     */
    private $nbBulletins;

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

    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->inProgress = false;
        $this->signed = false;
        $this->error = false;
        $this->disabled = false;
        $this->nbBulletins = 0;
        $this->verified = false;
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
     * Set uidUpload
     *
     * @param string $uidUpload
     * @return MassUpload
     */
    public function setUidUpload($uidUpload)
    {
        $this->uidUpload = $uidUpload;

        return $this;
    }

    /**
     * Get uidUpload
     *
     * @return string
     */
    public function getUidUpload()
    {
        return $this->uidUpload;
    }

    /**
     * Set month
     *
     * @param string $month
     * @return MassUpload
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
     * @return MassUpload
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return MassUpload
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
     * Set verified
     *
     * @param boolean $verified
     * @return MassUpload
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * Get verified
     *
     * @return boolean
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Set company
     *
     * @param \FrontBundle\Entity\Company $company
     * @return MassUpload
     */
    public function setCompany(\FrontBundle\Entity\Company $company)
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
     * Set nbBulletins
     *
     * @param string $nbBulletins
     * @return MassUpload
     */
    public function setNbBulletins($nbBulletins)
    {
        $this->nbBulletins = $nbBulletins;

        return $this;
    }

    /**
     * Get nbBulletins
     *
     * @return string
     */
    public function getNbBulletins()
    {
        return $this->nbBulletins;
    }

    /**
     * Set signed
     *
     * @param boolean $signed
     * @return MassUpload
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
     * @return MassUpload
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
     * @return MassUpload
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
     * @return MassUpload
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
     * Set disabled
     *
     * @param boolean $disabled
     * @return MassUpload
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
}
