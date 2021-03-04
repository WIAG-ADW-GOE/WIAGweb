<?php

namespace App\Entity;

use App\Repository\CnNamelookupRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnNamelookupRepository::class)
 */
class CnNamelookup
{

    /**
     * @ORM\ManyToOne(targetEntity="Canon", inversedBy="namelookup")
     * @ORM\JoinColumn(name="id_canon", referencedColumnName="id")
     */
    private $canon;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $idCanon;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $givenname;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $prefixName;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $familyname;

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

    public function getGivenname(): ?string
    {
        return $this->givenname;
    }

    public function setGivenname(string $givenname): self
    {
        $this->givenname = $givenname;

        return $this;
    }

    public function getPrefixName(): ?string
    {
        return $this->prefixName;
    }

    public function setPrefixName(string $prefixName): self
    {
        $this->prefixName = $prefixName;

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
