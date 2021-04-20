<?php

namespace App\Entity;

use App\Entity\Canon;
use App\Repository\OfficeSortkeyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnOfficeSortkeyRepository::class)
 * @ORM\Table(name="cn_officesortkey")
 */
class CnOfficeSortkey {

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=63)
     */
    private $id_online;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $id_office;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $location_name;

    /**
     * @ORM\Column(type="integer")
     */
    private $numdate_start;

    /**
     * @ORM\Column(type="integer")
     */
    private $numdate_end;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdOnline(): ?string
    {
        return $this->id_canon;
    }

    public function getLocationName(): ?string {
        return $this->location_name;
    }

    public function getNumdateStart(): ?int {
        return $this->numdate_start;
    }

    public function getNumdateEnd(): ?int {
        return $this->numdate_end;
    }

}
