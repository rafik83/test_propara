<?php
namespace BackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="LogZipRepository")
 * @ORM\Table(name="log_zip")
 *
 */
class LogZip {


    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(name="etape", type="string")
     */
    private $etape;
    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    /**
     * @ORM\Column(name="statut", type="boolean" )
     */
    private $statut;
    /**
     * @ORM\ManyToOne(targetEntity="ZipFile", cascade={"persist"})
     * @ORM\JoinColumn(name="zipfile_id", nullable=false)
     */
    private $zip;



    public function __construct()
    {

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
     * Set etape
     *
     * @param string $etape
     * @return LogZip
     */
    public function setEtape($etape)
    {
        $this->etape = $etape;

        return $this;
    }

    /**
     * Get etape
     *
     * @return string 
     */
    public function getEtape()
    {
        return $this->etape;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return LogZip
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
     * Set statut
     *
     * @param boolean $statut
     * @return LogZip
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut
     *
     * @return boolean 
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set zip
     *
     * @param \BackBundle\Entity\ZipFile $zip
     * @return LogZip
     */
    public function setZip(\BackBundle\Entity\ZipFile $zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return \BackBundle\Entity\ZipFile 
     */
    public function getZip()
    {
        return $this->zip;
    }
}
