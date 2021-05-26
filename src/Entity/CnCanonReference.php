<?php

namespace App\Entity;

use App\Repository\CnCanonReferenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnCanonReferenceRepository::class)
 */
class CnCanonReference
{

    /**
     * @ORM\ManyToOne(targetEntity="Canon", inversedBy="references")
     * @ORM\JoinColumn(name="id_canon", referencedColumnName="id")
     */
    private $canon;


    /**
     * @ORM\ManyToOne(targetEntity="CnReference")
     * @ORM\JoinColumn(name="id_reference", referencedColumnName="id")
     */
    private $reference;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $idCanon;

    /**
     * @ORM\Column(type="integer")
     */
    private $idReference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $itemReference;

    /**
     * @ORM\Column(type="string", length=511, nullable=true)
     */
    private $pageReference;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isbio;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $idInReference;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCanon(): ?string
    {
        return $this->idCanon;
    }

    public function setIdCanon(string $idCanon): self
    {
        $this->idCanon = $idCanon;

        return $this;
    }

    public function getIdReference(): ?int
    {
        return $this->idReference;
    }

    public function setIdReference(int $idReference): self
    {
        $this->idReference = $idReference;

        return $this;
    }

    public function getItemReference(): ?string
    {
        return $this->itemReference;
    }

    public function setItemReference(?string $itemReference): self
    {
        $this->itemReference = $itemReference;

        return $this;
    }

    public function getPageReference(): ?string
    {
        return $this->pageReference;
    }

    public function setPageReference(?string $pageReference): self
    {
        $this->pageReference = $pageReference;

        return $this;
    }

    public function getReference(): ?object
    {
        return $this->reference;
    }

    public function getIsbio(): ?bool
    {
        return $this->isbio;
    }

    public function setIsbio(bool $isbio): self
    {
        $this->isbio = $isbio;

        return $this;
    }

    // only relevant for CnCanonReferenceGS
    static public function getPageBio(): ?string {
        return null;
    }

    public function getIdInReference(): ?string
    {
        return $this->idInReference;
    }

    public function setIdInReference(?string $idInReference): self
    {
        $this->idInReference = $idInReference;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }


}
