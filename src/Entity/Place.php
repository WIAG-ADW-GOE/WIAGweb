<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlaceRepository::class)
 */
class Place
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id_places;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $place_name;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $gemeinde;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $diocese_id;

    /**
     * @ORM\OneToMany(targetEntity=MonasteryLocation::class, mappedBy="place")
     * @ORM\JoinColumn(name="id_places", referencedColumnName="place_id")
     */
    private $locations;

    
    public function getIdPlaces(): ?int
    {
        return $this->id_places;
    }

    public function setIdPlaces(int $id_places): self
    {
        $this->id_places = $id_places;

        return $this;
    }

    public function getPlaceName(): ?string
    {
        return $this->place_name;
    }

    public function setPlaceName(?string $place_name): self
    {
        $this->place_name = $place_name;

        return $this;
    }

    public function getGemeinde(): ?string
    {
        return $this->gemeinde;
    }

    public function setGemeinde(?string $gemeinde): self
    {
        $this->gemeinde = $gemeinde;

        return $this;
    }

    public function getDioceseId(): ?int
    {
        return $this->diocese_id;
    }

    public function setDioceseId(?int $diocese_id): self
    {
        $this->diocese_id = $diocese_id;

        return $this;
    }

    public function getLocationsWithPxlace() {
        return $this->locations;
    }
}
