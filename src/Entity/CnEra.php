<?php

namespace App\Entity;

use App\Repository\CnEraRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnEraRepository::class)
 */
class CnEra
{

    /**
     * @ORM\OneToOne(targetEntity="Canon", inversedBy="era")
     * @ORM\JoinColumn(name="id_canon", referencedColumnName="id")
     */
    private $canon;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=63)
     */
    private $idCanon;

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

    public function getIdCanon(): ?string
    {
        return $this->idCanon;
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
