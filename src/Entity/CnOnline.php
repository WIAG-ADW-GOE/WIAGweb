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
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idDh;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $id_gs;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $id_ep;


    /* fill these properties, for the list view or the detail view */

    private $canon_dh = null;

    private $canon_gs = null;

    private $bishop = null;

    private $offices_dh = null;

    private $offices_gs = null;

    private $offices_ep = null;

    private $references_dh = null;

    private $references_gs = null;

    private $references_ep = null;


    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $givenname;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $familyname;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $domstift;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $domstift_start;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $wiagid;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIdDh(): ?string
    {
        return $this->idDh;
    }

    public function setIdDh(?string $idDh): self
    {
        $this->idDh = $idDh;

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

    public function getIdEp(): ?string
    {
        return $this->id_ep;
    }

    public function setIdEp(?string $id_ep): self
    {
        $this->id_ep = $id_ep;

        return $this;
    }


    public function getCanonDh() {
        return $this->canon_dh;
    }

    public function setCanonDh($canon) {
        $this->canon_dh = $canon;
        return $this;
    }

    public function getCanonGs() {
        return $this->canon_gs;
    }

    public function setCanonGs($canon) {
        $this->canon_gs = $canon;
        return $this;
    }

    public function getOfficesDh() {
        return $this->offices_dh;
    }

    public function setOfficesDh($officesdh) {
        $this->offices_dh = $officesdh;
        return $this;
    }

    public function getOfficesGs() {
        if (!is_null($this->offices_gs)) {
            return $this->offices_gs;
        } elseif (!is_null($this->canon_gs)) {
            return $this->canon_gs->getOffices();
        } else {
            return null;
        }
    }

    public function setOfficesGs($officesgs) {
        $this->offices_gs = $officesgs;
        return $this;
    }

    public function getOfficesEp() {
        return $this->offices_ep;
    }

    public function setOfficesEp($officesep) {
        $this->offices_ep = $officesep;
        return $this;
    }


    public function getReferencesDh() {
        return $this->references_dh;
    }

    public function setReferencesDh($referencesdh) {
        $this->references_dh = $referencesdh;
        return $this;
    }

    public function getReferencesGs() {
        if (!is_null($this->references_gs)) {
            return $this->references_gs;
        } elseif (!is_null($this->canon_gs)) {
            return $this->canon_gs->getReferences();
        } else {
            return null;
        }
    }

    public function setReferencesGs($referencesgs) {
        $this->references_gs = $referencesgs;
        return $this;
    }

    public function getReferencesEp() {
        return $this->references_ep;
    }

    public function setReferencesEp($referencesep) {
        $this->references_ep = $referencesep;
        return $this;
    }


    public function setBishop($bishop) {
        $this->bishop = $bishop;
        return $this;
    }

    public function getBishop() {
        return $this->bishop;
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

    public function getFamilyname(): ?string
    {
        return $this->familyname;
    }

    public function setFamilyname(?string $familyname): self
    {
        $this->familyname = $familyname;

        return $this;
    }

    public function getDomstift(): ?string
    {
        return $this->domstift;
    }

    public function setDomstift(?string $domstift): self
    {
        $this->domstift = $domstift;

        return $this;
    }

    public function getDomstiftStart(): ?int
    {
        return $this->domstift_start;
    }

    public function setDomstiftStart(?int $domstift_start): self
    {
        $this->domstift_start = $domstift_start;

        return $this;
    }

    public function getWiagid(): ?string
    {
        return $this->wiagid;
    }

    public function setWiagid(string $wiagid): self
    {
        $this->wiagid = $wiagid;

        return $this;
    }


}
