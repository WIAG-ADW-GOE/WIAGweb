<?php

namespace App\Entity;

use App\Repository\CnEraRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnEraRepository::class)
 */
class CnEra {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $idOnline;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $eraStart;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $eraEnd;

    public function getCanon() {
        return $this->canon;
    }

    public function setIdOnline($idonline): self {
        $this->idOnline = $idonline;
        return $this;
    }

    public function getIdOnline(): ?string
    {
        return $this->idOnline;
    }

    public function getEraStart(): ?int
    {
        return $this->eraStart;
    }

    public function setEraStart(?int $eraStart): self
    {
        $this->eraStart = $eraStart;

        return $this;
    }

    public function getEraEnd(): ?int
    {
        return $this->eraEnd;
    }

    public function setEraEnd(?int $eraEnd): self
    {
        $this->eraEnd = $eraEnd;

        return $this;
    }
}
