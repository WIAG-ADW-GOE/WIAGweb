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
     * @ORM\ManyToOne(targetEntity="CnOnline", inversedBy="namelookup")
     * @ORM\JoinColumn(name="id_online", referencedColumnName="id")
     */
    private $cnonline;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_online;

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


    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $gn_fn;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $gn_prefix_fn;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdOnline(): ?string {
        return $this->id_online;
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

    public function getGnFn(): ?string
    {
        return $this->gn_fn;
    }

    public function setGnFn(?string $gn_fn): self
    {
        $this->gn_fn = $gn_fn;

        return $this;
    }

    public function getGnPrefixFn(): ?string
    {
        return $this->gn_prefix_fn;
    }

    public function setGnPrefixFn(?string $gn_prefix_fn): self
    {
        $this->gn_prefix_fn = $gn_prefix_fn;

        return $this;
    }
}
