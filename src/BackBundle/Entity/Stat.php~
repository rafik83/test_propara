<?php
namespace BackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="StatRepository")
 * @ORM\Table(name="stat")
 */
class Stat {


    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(name="size_docs", type="string", length=100, nullable=true)
     */
    private $sizeDocs;
    /**
     * @ORM\Column(name="size_bulletins", type="string", length=100, nullable=true)
     */
    private $sizeBulletins;
    /**
     * @ORM\Column(name="update_at", type="datetime")
     */
    private $updateAt;


    public function __construct()
    {
        $this->updateAt = new \Datetime();
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
     * Set sizeDocs
     *
     * @param string $sizeDocs
     * @return Stat
     */
    public function setSizeDocs($sizeDocs)
    {
        $this->sizeDocs = $sizeDocs;

        return $this;
    }

    /**
     * Get sizeDocs
     *
     * @return string 
     */
    public function getSizeDocs()
    {
        return $this->sizeDocs;
    }

    /**
     * Set sizeBulletins
     *
     * @param string $sizeBulletins
     * @return Stat
     */
    public function setSizeBulletins($sizeBulletins)
    {
        $this->sizeBulletins = $sizeBulletins;

        return $this;
    }

    /**
     * Get sizeBulletins
     *
     * @return string 
     */
    public function getSizeBulletins()
    {
        return $this->sizeBulletins;
    }

    /**
     * Set updateAt
     *
     * @param \DateTime $updateAt
     * @return Stat
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    /**
     * Get updateAt
     *
     * @return \DateTime 
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }
}
