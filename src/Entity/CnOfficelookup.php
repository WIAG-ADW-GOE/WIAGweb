<?php

namespace App\Entity;

use App\Repository\CnOfficelookupRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnOfficelookupRepository::class)
 */
class CnOfficelookup {

    /**
     * @ORM\ManyToOne(targetEntity="CnOnline", inversedBy="officelookup")
     * @ORM\JoinColumn(name="id_online", referencedColumnName="id")
     */
    private $cnonline;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="string", length=31)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $id_online;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $office_name;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $location_name;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $id_monastery;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numdate_start;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numdate_end;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCanon(): ?string
    {
        return $this->id_canon;
    }

    public function setIdCanon(string $id_canon): self
    {
        $this->id_canon = $id_canon;

        return $this;
    }

    public function getOfficeName(): ?string
    {
        return $this->office_name;
    }

    public function setOfficeName(?string $office_name): self
    {
        $this->office_name = $office_name;

        return $this;
    }

    public function getLocationName(): ?string
    {
        return $this->location_name;
    }

    public function setLocationName(?string $location_name): self
    {
        $this->location_name = $location_name;

        return $this;
    }

    public function getIdMonastery(): ?string
    {
        return $this->id_monastery;
    }

    public function setIdMonastery(?string $id_monastery): self
    {
        $this->id_monastery = $id_monastery;

        return $this;
    }

    public function getNumdateStart(): ?int
    {
        return $this->numdate_start;
    }

    public function setNumdateStart(?int $numdate_start): self
    {
        $this->numdate_start = $numdate_start;

        return $this;
    }

    public function getNumdateEnd(): ?int
    {
        return $this->numdate_end;
    }

    public function setNumdateEnd(?int $numdate_end): self
    {
        $this->numdate_end = $numdate_end;

        return $this;
    }
}
