<?php
namespace BackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="printDepotsRepository")
 * @ORM\Table(name="print_depots")
 *
 */
class PrintDepots
{


    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    /**
     * @ORM\Column(name="filename", type="string" )
     */
    private $filename;
    /**
     * @ORM\ManyToOne(targetEntity="MassUpload", cascade={"persist"})
     * @ORM\JoinColumn(name="mu_id", nullable=true)
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return PrintDepots
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
     * Set filename
     *
     * @param string $filename
     * @return PrintDepots
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set mu
     *
     * @param \BackBundle\Entity\MassUpload $mu
     * @return PrintDepots
     */
    public function setMu(\BackBundle\Entity\MassUpload $mu = null)
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
