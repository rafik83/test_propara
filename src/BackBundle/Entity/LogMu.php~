<?php
namespace BackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="LogMuRepository")
 * @ORM\Table(name="log_mu")
 *
 */
class LogMu
{


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
     * @ORM\ManyToOne(targetEntity="MassUpload", cascade={"persist"})
     * @ORM\JoinColumn(name="mu_id", nullable=false)
     */
    private $mu;


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
     * @return LogMu
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
     * @return LogMu
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
     * @return LogMu
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
     * Set mu
     *
     * @param \BackBundle\Entity\MassUpload $mu
     * @return LogMu
     */
    public function setMu(\BackBundle\Entity\MassUpload $mu)
    {
        $this->mu = $mu;

        return $this;
    }

    /**
     * Get mu
     *
     * @return \BackBundle\Entity\MassUpload 
     */
    public function getMu()
    {
        return $this->mu;
    }
}
