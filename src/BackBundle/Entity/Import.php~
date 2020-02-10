<?php
/*
 * This file is part of the fulldon project
 *
 * (c) SAMI BOUSSACSOU <boussacsou@intersa.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Table(name="import")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="BackBundle\Entity\ImportRepository")
 */
class Import
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(name="columns", type="text",nullable=false)
     */
    private $columns;
    /**
     * @ORM\Column(name="importFile", type="string", length=255,nullable=false)
     * @Assert\File(mimeTypes={ "text/csv", "text/plain"})
     */
    private $importFile;
    // 0: Waiting , 1: in progress, 2: done
    /**
     * @ORM\Column(name="progress", type="integer", nullable=true)
     */
    private $progress;
    /**
     * @ORM\Column(name="separateur", type="string", length=255, nullable=false)
     */
    private $separateur;

    /**
     * @ORM\Column(name="format_date", type="string", length=255, nullable=false)
     */
    private $formatDate;
    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    /**
     * @ORM\Column(name="comment_error", type="text" , nullable = true)
     */
    private $commentError;

    public function __construct() {
        $this->progress = 0;
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
     * Set columns
     *
     * @param string $columns
     * @return Import
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get columns
     *
     * @return string 
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set importFile
     *
     * @param string $importFile
     * @return Import
     */
    public function setImportFile($importFile)
    {
        $this->importFile = $importFile;

        return $this;
    }

    /**
     * Get importFile
     *
     * @return string 
     */
    public function getImportFile()
    {
        return $this->importFile;
    }



    /**
     * Set done
     *
     * @param boolean $done
     * @return Import
     */
    public function setDone($done)
    {
        $this->done = $done;

        return $this;
    }

    /**
     * Get done
     *
     * @return boolean 
     */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * Set progress
     *
     * @param integer $progress
     * @return Import
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress
     *
     * @return integer 
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set separateur
     *
     * @param string $separateur
     * @return Import
     */
    public function setSeparateur($separateur)
    {
        $this->separateur = $separateur;

        return $this;
    }

    /**
     * Get separateur
     *
     * @return string 
     */
    public function getSeparateur()
    {
        return $this->separateur;
    }

    /**
     * Set formatDate
     *
     * @param string $formatDate
     * @return Import
     */
    public function setFormatDate($formatDate)
    {
        $this->formatDate = $formatDate;

        return $this;
    }

    /**
     * Get formatDate
     *
     * @return string 
     */
    public function getFormatDate()
    {
        return $this->formatDate;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Import
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
     * Set commentError
     *
     * @param string $commentError
     * @return Import
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
}
