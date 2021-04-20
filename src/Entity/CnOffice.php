<?php

namespace App\Entity;

use App\Repository\CnOfficeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnOfficeRepository::class)
 */
class CnOffice
{
    /**
     * @ORM\ManyToOne(targetEntity="Canon", inversedBy="offices")
     * @ORM\JoinColumn(name="id_canon", referencedColumnName="id")
     */
    private $canon;

    /**
     * @ORM\OneToOne(targetEntity="CnOfficedate", mappedBy="office")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_office")
     */
    private $numdate;

    /**
     * @ORM\ManyToOne(targetEntity="Monastery", inversedBy="office")
     * @ORM\JoinColumn(nullable=true, name="id_monastery", referencedColumnName="wiagid")
     */
    private $monastery;

    /**
     * if an office is at a monastery we lookup it's place(s) (see CnOfficeRepository)
     */
    private $monasterylocationstr;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="string", length=31)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $diocese;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $officeName;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $dateStart;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $dateEnd;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $idCanon;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $institution;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $officeMundane;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $dominion;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=1023, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sortkey;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $profession;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $idMonastery;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $idInReference;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $dignity;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $location_show;

    public function getNumdate() {
        return $this->numdate;
    }

    public function getMonasterylocationstr()
    {
        return $this->monasterylocationstr;
    }

    public function setMonasterylocationstr($monasterylocationstr): self
    {
        $this->monasterylocationstr = $monasterylocationstr;

        return $this;
    }

    public function getMonastery()
    {
        return $this->monastery;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDiocese(): ?string
    {
        return $this->diocese;
    }

    public function setDiocese(?string $diocese): self
    {
        $this->diocese = $diocese;

        return $this;
    }

    public function getOfficeName(): ?string
    {
        return $this->officeName;
    }

    public function setOfficeName(?string $officeName): self
    {
        $this->officeName = $officeName;

        return $this;
    }

    public function getDateStart(): ?string
    {
        return $this->dateStart;
    }

    public function setDateStart(?string $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?string
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?string $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
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

    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    public function setInstitution(?string $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function getOfficeMundane(): ?string
    {
        return $this->officeMundane;
    }

    public function setOfficeMundane(?string $officeMundane): self
    {
        $this->officeMundane = $officeMundane;

        return $this;
    }

    public function getDominion(): ?string
    {
        return $this->dominion;
    }

    public function setDominion(?string $dominion): self
    {
        $this->dominion = $dominion;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

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

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getIdMonastery(): ?string
    {
        return $this->idMonastery;
    }

    public function setIdMonastery(?string $idMonastery): self
    {
        $this->idMonastery = $idMonastery;

        return $this;
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

    public function getDignity(): ?string
    {
        return $this->dignity;
    }

    public function setDignity(?string $dignity): self
    {
        $this->dignity = $dignity;

        return $this;
    }

    public function getLocationShow(): ?string
    {
        return $this->location_show;
    }

    public function setLocationShow(?string $location_show): self
    {
        $this->location_show = $location_show;

        return $this;
    }
}
