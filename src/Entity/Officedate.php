<?php

namespace App\Entity;

use App\Repository\OfficedateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OfficedateRepository::class)
 */
class Officedate
{

    /**
     * @ORM\id
     * @ORM\OneToOne(targetEntity="Office", inversedBy="numdate")
     * @ORM\JoinColumn(name="wiagid_office", referencedColumnName="wiagid")
     */
    private $wiagid_office;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $date_start;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $date_end;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWiagidOffice(): ?string
    {
        return $this->wiagid_office;
    }

    public function setWiagidOffice(string $wiagid_office): self
    {
        $this->wiagid_office = $wiagid_office;

        return $this;
    }

    public function getDateStart(): ?int
    {
        return $this->date_start;
    }

    public function setDateStart(?int $date_start): self
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?int
    {
        return $this->date_end;
    }

    public function setDateEnd(?int $date_end): self
    {
        $this->date_end = $date_end;

        return $this;
    }
}
