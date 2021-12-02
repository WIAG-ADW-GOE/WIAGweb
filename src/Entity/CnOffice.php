<?php

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CnOfficeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CnOfficeRepository::class)
 */
class CnOffice {

    /**
     * @ORM\ManyToOne(targetEntity="Canon", inversedBy="offices")
     * @ORM\JoinColumn(name="id_canon", referencedColumnName="id")
     */
    private $canon;

    /**
     * @ORM\OneToOne(targetEntity="Monastery")
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
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $diocese;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     * @Assert\NotBlank(message="Bitte Feld 'Amtsart' befüllen.")
     */
    private $officeName;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     * @Assert\LessThan(2021, message="Die Amtszeit muss in der Vergangenheit beginnen.")
     */
    private $dateStart;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $dateEnd;

    /**
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="integer", nullable=true)
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

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $archdeacon_territory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numdate_start;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numdate_end;

    /**
     * monastery name in edit form
     */
    private $form_monastery_name;

    /**
     * number of monastries for $form_monastery_name
     */
    private $n_monasteries;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $dioceseInDb;

    public function setCanon(Canon $canon): self {
        $this->canon = $canon;
        return $this;
    }

    public function setMonastery(Monastery $monastery): self {
        $this->monastery = $monastery;
        return $this;
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

    public function getId(): ?int
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

    public function getIdCanon(): ?int
    {
        return $this->idCanon;
    }

    public function setIdCanon(int $idCanon): self
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

    public function getArchdeaconTerritory(): ?string
    {
        return $this->archdeacon_territory;
    }

    public function setArchdeaconTerritory(?string $archdeacon_territory): self
    {
        $this->archdeacon_territory = $archdeacon_territory;

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

    public function getFormMonasteryName(): ?string {
        return $this->form_monastery_name;
    }

    public function setFormMonasteryName($name): self {
        $this->form_monastery_name = $name;
        return $this;
    }

    public function getNMonasteries(): ?int {
        return $this->n_monasteries;
    }

    public function setNMonasteries(?int $n): self {
        $this->n_monasteries = $n;
        return $this;
    }

    /**
     * return sorting value based on dates
     */
    public function numdateSort(): int {
        $s = 0;
        $datestart = $this->numdate_start;
        $dateend = $this->numdate_end;
        if (!is_null($datestart)) {
            $s += $datestart * 10000;
        }
        if (!is_null($dateend)) {
            $s += $dateend;
        }
        return $s;
    }

    /**
     * @Assert\IsTrue(message="Kloster gibt es nicht oder es ist nicht eindeutig")
     */
    public function isValidMonastery() {
        $n = $this->n_monasteries;

        if (is_null($n) || $n == 1) {
            return true;
        }

        return false;
    }

    public function getDioceseInDb(): ?bool
    {
        return $this->dioceseInDb;
    }

    public function setDioceseInDb(?bool $dioceseInDb): self
    {
        $this->dioceseInDb = $dioceseInDb;

        return $this;
    }

    public function showDate(): ?string {
        if(!$this->dateStart && !$this->dateEnd) {
            return null;
        }
        if(!$this->dateStart) {
            return "bis ".$this->dateEnd;
        }
        if(!$this->dateEnd) {
            return $this->dateStart;
        }

        return $this->dateStart.'-'.$this->dateEnd;
    }

}
