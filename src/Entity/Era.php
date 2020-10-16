<?php

namespace App\Entity;

use App\Repository\EraRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EraRepository::class)
 */
class Era
{

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Person", inversedBy="era")
     * @ORM\JoinColumn(name="wiagid_person", referencedColumnName="wiagid")
     */
    private $wiagid_person;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $era_start;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $era_end;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWiagidPerson(): ?string
    {
        return $this->wiagid_person;
    }

    public function setWiagidPerson(string $wiagid_person): self
    {
        $this->wiagid_person = $wiagid_person;

        return $this;
    }

    public function getEraStart(): ?int
    {
        return $this->era_start;
    }

    public function setEraStart(?int $era_start): self
    {
        $this->era_start = $era_start;

        return $this;
    }

    public function getEraEnd(): ?int
    {
        return $this->era_end;
    }

    public function setEraEnd(?int $era_end): self
    {
        $this->era_end = $era_end;

        return $this;
    }
}
