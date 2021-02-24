<?php

namespace App\Entity;

use App\Entity\Canon;
use App\Repository\OfficeSortkeyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnOfficeSortkeyRepository::class)
 * @ORM\Table(name="cn_officesortkey")
 */
class CnOfficeSortkey
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $id_canon;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $diocese;

    /**
     * @ORM\Column(type="integer")
     */
    private $sortkey;

    /**
     * @ORM\ManyToOne(targetEntity=Canon::class, inversedBy="officeSortkeys")
     * @ORM\JoinColumn(name="id_canon", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=false)
     */
    private $canon;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCanon(): ?string
    {
        return $this->id_canon;
    }

    public function getDiocese(): ?string
    {
        return $this->diocese;
    }

    public function setDiocese(string $diocese): self
    {
        $this->diocese = $diocese;

        return $this;
    }

    public function getSortkey(): ?int
    {
        return $this->sortkey;
    }

    public function setSortkey(int $sortkey): self
    {
        $this->sortkey = $sortkey;

        return $this;
    }

    public function getCanon(): ?Canon
    {
        return $this->canon;
    }

    public function setCanon(?Canon $canon): self
    {
        $this->canon = $canon;

        return $this;
    }
}
