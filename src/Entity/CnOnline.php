<?php

namespace App\Entity;

use App\Repository\CnOnlineRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnOnlineRepository::class)
 */
class CnOnline {

    /**
     * @ORM\OneToMany(targetEntity="CnNamelookup", mappedBy="cnonline")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_online")
     */
    private $namelookup;

    /**
     * @ORM\OneToMany(targetEntity="CnOfficelookup", mappedBy="cnonline")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_online")
     */
    private $officelookup;

    /**
     * @ORM\OneToOne(targetEntity="CnOfficeSortkey")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_online")
     */
    private $officesortkey;

    /**
     * @ORM\OneToOne(targetEntity="CnEra")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_online")
     */
    private $era;

    /**
     * @ORM\OneToMany(targetEntity="CnIdlookup", mappedBy="cnonline")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_online")
     */
    private $idlookup;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="string", length=63)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $id_dh;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $id_gs;

    private $canon_dh = null;

    private $canon_gs = null;

    private $offices_dh = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIdDh(): ?string
    {
        return $this->id_dh;
    }

    public function setIdDh(?string $id_dh): self
    {
        $this->id_dh = $id_dh;

        return $this;
    }

    public function getIdGs(): ?string
    {
        return $this->id_gs;
    }

    public function setIdGs(?string $id_gs): self
    {
        $this->id_gs = $id_gs;

        return $this;
    }

    public function getCanonDh() {
        return $this->canon_dh;
    }

    public function setCanonDh($canon) {
        $this->canon_dh = $canon;
        return $this;
    }

    public function getOfficesDh() {
        return $this->offices_dh;
    }

    public function setOfficesDh($officesdh) {
        $this->offices_dh = $officesdh;
        return $this;
    }

    public function getCanonGs() {
        return $this->canon_gs;
    }

    public function setCanonGs($canon) {
        $this->canon_gs = $canon;
        return $this;
    }

    public function getOfficesGs() {
        return $this->offices_gs;
    }

    public function setOfficesGs($officesgs) {
        $this->offices_gs = $officesgs;
        return $this;
    }

}
