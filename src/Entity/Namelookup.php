<?php

namespace App\Entity;

use App\Repository\NamelookupRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NamelookupRepository::class)
 */
class Namelookup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="namelookup")
     * @ORM\JoinColumn(name="wiagid_person", referencedColumnName="wiagid")
     */
    private $wiagid_person;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $givenname;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $prefix_name;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $familyname;

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

    public function getGivenname(): ?string
    {
        return $this->givenname;
    }

    public function setGivenname(?string $givenname): self
    {
        $this->givenname = $givenname;

        return $this;
    }

    public function getPrefixName(): ?string
    {
        return $this->prefix_name;
    }

    public function setPrefixName(?string $prefix_name): self
    {
        $this->prefix_name = $prefix_name;

        return $this;
    }

    public function getFamilyname(): ?string
    {
        return $this->familyname;
    }

    public function setFamilyname(?string $familyname): self
    {
        $this->familyname = $familyname;

        return $this;
    }
}
