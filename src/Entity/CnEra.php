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
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $eraStart;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $eraEnd;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $domstift;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $domstift_start;

    public function getId(): int
    {
        return $this->id;
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

    public function getDomstift(): ?string
    {
        return $this->domstift;
    }

    public function setDomstift(?string $domstift): self
    {
        $this->domstift = $domstift;

        return $this;
    }

    public function getDomstiftStart(): ?int
    {
        return $this->domstift_start;
    }

    public function setDomstiftStart(?int $domstift_start): self
    {
        $this->domstift_start = $domstift_start;

        return $this;
    }
}
