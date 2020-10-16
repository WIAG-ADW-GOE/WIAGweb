<?php
// src/Entity/Office.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OfficeRepository")
 */
class Office
{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=63, nullable = false)
     */
    private $wiagid;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="offices")
     * @ORM\JoinColumn(name="wiagid_person", referencedColumnName="wiagid")
     */
    private $wiagid_person;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $office_name;

    /**
     * @ORM\Column(type="string", length=31, nullable = false)
     */
    private $date_start;

    /**
     * @ORM\Column(type="string", length=31, nullable = true)
     */
    private $date_end;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $diocese;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $institution;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $office_mundane;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $dominion;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $id_monastery;

    /**
     * @ORM\OneToOne(targetEntity="Officedate", mappedBy="wiagid_office")
     * @ORM\JoinColumn(name="wiagid", referencedColumnName="wiagid_office")
     */
    private $numdate;

    public function getWiagid(): ?string
    {
        return $this->wiagid;
    }

    public function setWiagid(string $wiagid): self
    {
        $this->wiagid = $wiagid;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateStart(): ?string
    {
        return $this->date_start;
    }

    public function setDateStart(string $date_start): self
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?string
    {
        return $this->date_end;
    }

    public function setDateEnd(?string $date_end): self
    {
        $this->date_end = $date_end;

        return $this;
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
        return $this->office_mundane;
    }

    public function setOfficeMundane(?string $office_mundane): self
    {
        $this->office_mundane = $office_mundane;

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

    public function getOfficeName(): ?string
    {
        return $this->office_name;
    }

    public function setOfficeName(?string $office_name): self
    {
        $this->office_name = $office_name;

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

    public function getNumdate()
    {
        return $this->numdate;
    }

    public function setNumdate($numdate): self
    {
        $this->numdate = $numdate;

        return $this;
    }

    public function toJSON() {
        $ocj = array();
        
        $ocj['officeTitle'] = $this->getOfficeName();

        $fv = $this->getDiocese();
        if($fv) $ocj['diocese'] = $fv;
        
        $fv = $this->getDateStart();
        if($fv) $ocj['dateStart'] = $fv;
        
        $fv = $this->getDateEnd();
        if($fv) $ocj['dateEnd'] = $fv;
        
        $fv = $this->getComment();
        if($fv) $ocj['comment'] = $fv;
        
        return $ocj;
    }
    
    
}
