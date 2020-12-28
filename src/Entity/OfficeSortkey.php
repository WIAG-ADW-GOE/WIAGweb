<?php

namespace App\Entity;

use App\Repository\OfficeSortkeyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OfficeSortkeyRepository::class)
 * @ORM\Table(name="officesortkey")
 */
class OfficeSortkey
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $wiagid_person;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $diocese;

    /**
     * @ORM\Column(type="integer")
     */
    private $sortkey;

    /**
     * @ORM\ManyToOne(targetEntity=Person::class, inversedBy="officeSortkeys")
     * @ORM\JoinColumn(name="wiagid_person", referencedColumnName="wiagid")
     * @ORM\JoinColumn(nullable=false)
     */
    private $person;

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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }
}
