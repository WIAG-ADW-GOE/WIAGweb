<?php

namespace App\Entity;

use App\Repository\MonasteryLocationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MonasteryLocationRepository::class)
 * @ORM\Table(name="monastery_location")
 */
class MonasteryLocation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $wiagid_monastery;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $location_name;

    /**
     * @ORM\ManyToOne(targetEntity=Monastery::class, inversedBy="locations")
     * @ORM\JoinColumn(name="wiagid_monastery", referencedColumnName="wiagid", nullable=false)
     */
    private $monastery;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $place_id;

    /**
     * @ORM\ManyToOne(targetEntity=Place::class, inversedBy="locations")
     * @ORM\JoinColumn(name="place_id", referencedColumnName="id_places")
     */
    private $place;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $location_begin_tpq;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $location_end_tpq;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWiagidMonastery(): ?string
    {
        return $this->wiagid_monastery;
    }

    public function setWiagidMonastery(string $wiagid_monastery): self
    {
        $this->wiagid_monastery = $wiagid_monastery;

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

    public function getMonastery(): ?Monastery
    {
        return $this->monastery;
    }

    public function setMonastery(?Monastery $monastery): self
    {
        $this->monastery = $monastery;

        return $this;
    }

    public function getPlaceId(): ?string
    {
        return $this->place_id;
    }

    public function setPlaceId(?string $place_id): self
    {
        $this->place_id = $place_id;

        return $this;
    }

    public function getPlace() {
        return $this->place;
    }

    public function getLocationBeginTpq(): ?string
    {
        return $this->location_begin_tpq;
    }

    public function setLocationBeginTpq(?string $location_begin_tpq): self
    {
        $this->location_begin_tpq = $location_begin_tpq;

        return $this;
    }

    public function getLocationEndTpq(): ?string
    {
        return $this->location_end_tpq;
    }

    public function setLocationEndTpq(?string $location_end_tpq): self
    {
        $this->location_end_tpq = $location_end_tpq;

        return $this;
    }

}
