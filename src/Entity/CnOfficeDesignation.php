<?php

namespace App\Entity;

use App\Repository\CnOfficeDesignationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnOfficeDesignationRepository::class)
 */
class CnOfficeDesignation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $nameSingular;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $namePlural;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sortkey;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameSingular(): ?string
    {
        return $this->nameSingular;
    }

    public function setNameSingular(string $nameSingular): self
    {
        $this->nameSingular = $nameSingular;

        return $this;
    }

    public function getNamePlural(): ?string
    {
        return $this->namePlural;
    }

    public function setNamePlural(?string $namePlural): self
    {
        $this->namePlural = $namePlural;

        return $this;
    }

    public function getSortkey(): ?int
    {
        return $this->sortkey;
    }

    public function setSortkey(?int $sortkey): self
    {
        $this->sortkey = $sortkey;

        return $this;
    }
}
