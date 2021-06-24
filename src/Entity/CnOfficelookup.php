<?php

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
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
    private $cnOnline;

    public function setCnOnline(CnOnline $co): self {
        $this->cnOnline = $co;
        return $this;
    }

    /**
     * @ORM\OneToOne(targetEntity="Monastery")
     * @ORM\JoinColumn(nullable=true, name="id_monastery", referencedColumnName="wiagid")
     */
    private $monastery;


    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=31)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $officeName;

    /**
     * @ORM\Column(type="integer")
     */
    private $idOnline;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idMonastery;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $locationName;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $archdeaconTerritory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numdateStart;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numdateEnd;


    public function setMonastery(Monastery $monastery): self {
        $this->monastery = $monastery;
        return $this;
    }

    public function getMonastery()
    {
        return $this->monastery;
    }

    public function setId(string $id): self {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?string {
        return $this->id;
    }


    public function getOfficeName(): ?string {
        return $this->officeName;
    }

    public function setOfficeName(?string $officeName): self {
        $this->officeName = $officeName;

        return $this;
    }

    public function getIdOnline(): ?int {
        return $this->idOnline;
    }

    public function setIdOnline(int $idOnline): self {
        $this->idOnline = $idOnline;

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


    public function getLocationName(): ?string
    {
        return $this->locationName;
    }

    public function setLocationName(?string $location): self
    {
        $this->locationName = $location;

        return $this;
    }

    public function getArchdeaconTerritory(): ?string
    {
        return $this->archdeaconTerritory;
    }

    public function setArchdeaconTerritory(?string $territory): self
    {
        $this->archdeaconTerritory = $territory;

        return $this;
    }

    public function getNumdateStart(): ?int
    {
        return $this->numdateStart;
    }

    public function setNumdateStart(?int $start): self
    {
        $this->numdateStart = $start;

        return $this;
    }

    public function getNumdateEnd(): ?int
    {
        return $this->numdateEnd;
    }


    public function setNumdateEnd(?int $end): self
    {
        $this->numdateEnd = $end;

        return $this;
    }

}


// namespace App\Entity;

// use App\Repository\CnOfficelookupRepository;
// use Doctrine\ORM\Mapping as ORM;

// /**
//  * @ORM\Entity(repositoryClass=CnOfficelookupRepository::class)
//  */
// class CnOfficelookup {

//     /**
//      * @ORM\ManyToOne(targetEntity="CnOnline", inversedBy="officelookup")
//      * @ORM\JoinColumn(name="id_online", referencedColumnName="id")
//      */
//     private $cnOnline;

//     public function setCnOnline(CnOnline $co): self {
//         $this->cnOnline = $co;
//         return $this;
//     }

//     /**
//      * @ORM\OneToOne(targetEntity="Monastery")
//      * @ORM\JoinColumn(nullable=true, name="id_monastery", referencedColumnName="wiagid")
//      */
//     private $monastery;

//     public function setMonastery(Monastery $m): self {
//         $this->monastery = $m;
//         return $this;
//     }

//     public function getMonastery() {
//         return $this->monastery;
//     }


//     /**
//      * @ORM\ManyToOne(targetEntity="Domstift")
//      * @ORM\JoinColumn(nullable=true, name="id_monastery", referencedColumnName="gs_id")
//      */
//     private $domstift;

//     /**
//      * @ORM\Id
//      * @ORM\Column(type="string", length=31)
//      */
//     private $id;

//     /**
//      * @ORM\Column(type="string", length=31)
//      */
//     private $id_online;

//     /**
//      * @ORM\Column(type="string", length=127, nullable=true)
//      */
//     private $office_name;

//     /**
//      * @ORM\Column(type="string", length=63, nullable=true)
//      */
//     private $location_name;

//     /**
//      * @ORM\Column(type="integer", nullable=true)
//      */
//     private $id_monastery;

//     /**
//      * @ORM\Column(type="integer", nullable=true)
//      */
//     private $numdate_start;

//     /**
//      * @ORM\Column(type="integer", nullable=true)
//      */
//     private $numdate_end;

//     /**
//      * @ORM\Column(type="string", length=63, nullable=true)
//      */
//     private $archdeacon_territory;

//     public function setId(string $id): self
//     {
//         $this->id = $id;
//         return $this;
//     }


//     public function getId(): ?string
//     {
//         return $this->id;
//     }

//     public function getIdCanon(): ?string
//     {
//         return $this->id_canon;
//     }

//     public function setIdCanon(string $id_canon): self
//     {
//         $this->id_canon = $id_canon;

//         return $this;
//     }

//     public function getOfficeName(): ?string
//     {
//         return $this->office_name;
//     }

//     public function setOfficeName(?string $office_name): self
//     {
//         $this->office_name = $office_name;

//         return $this;
//     }

//     public function getLocationName(): ?string
//     {
//         return $this->location_name;
//     }

//     public function setLocationName(?string $location_name): self
//     {
//         $this->location_name = $location_name;

//         return $this;
//     }

//     public function getIdMonastery(): ?int
//     {
//         return $this->id_monastery;
//     }

//     public function setIdMonastery(?int $id_monastery): self
//     {
//         $this->id_monastery = $id_monastery;

//         return $this;
//     }

//     public function getNumdateStart(): ?int
//     {
//         return $this->numdate_start;
//     }

//     public function setNumdateStart(?int $numdate_start): self
//     {
//         $this->numdate_start = $numdate_start;

//         return $this;
//     }

//     public function getNumdateEnd(): ?int
//     {
//         return $this->numdate_end;
//     }

//     public function setNumdateEnd(?int $numdate_end): self
//     {
//         $this->numdate_end = $numdate_end;

//         return $this;
//     }

//     public function getArchdeaconTerritory(): ?string
//     {
//         return $this->archdeacon_territory;
//     }

//     public function setArchdeaconTerritory(?string $archdeacon_territory): self
//     {
//         $this->archdeacon_territory = $archdeacon_territory;

//         return $this;
//     }
// }
