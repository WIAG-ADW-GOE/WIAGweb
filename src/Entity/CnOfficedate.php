<?php

namespace App\Entity;

use App\Repository\CnOfficedateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnOfficedateRepository::class)
 */
class CnOfficedate
{

    /**
     * @ORM\OneToOne(targetEntity="CnOffice", inversedBy="numdate")
     * @ORM\JoinColumn(name="id_office", referencedColumnName="id")
     */
    private $office;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="string", length=31)
     */
    private $idOffice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dateStart;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dateEnd;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateStart(): ?int
    {
        return $this->dateStart;
    }

    public function setDateStart(?int $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?int
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?int $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }
}
