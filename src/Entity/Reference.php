<?php

namespace App\Entity;

use App\Repository\ReferenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReferenceRepository::class)
 */
class Reference
{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=31)
     */
    private $wiagid;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $bibshort;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $biblong;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWiagid(): ?string
    {
        return $this->wiagid;
    }

    public function setWiagid(string $wiagid): self
    {
        $this->wiagid = $wiagid;

        return $this;
    }

    public function getBibshort(): ?string
    {
        return $this->bibshort;
    }

    public function setBibshort(?string $bibshort): self
    {
        $this->bibshort = $bibshort;

        return $this;
    }

    public function getBiblong(): ?string
    {
        return $this->biblong;
    }

    public function setBiblong(?string $biblong): self
    {
        $this->biblong = $biblong;

        return $this;
    }
}
