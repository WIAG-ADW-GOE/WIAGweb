<?php

namespace App\Entity;

use App\Repository\DomstiftRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DomstiftRepository::class)
 */
class Domstift
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id_domstift;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $gs_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdDomstift(): ?int
    {
        return $this->id_domstift;
    }

    public function setIdDomstift(int $id_domstift): self
    {
        $this->id_domstift = $id_domstift;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGsId(): ?int
    {
        return $this->gs_id;
    }

    public function setGsId(int $gs_id): self
    {
        $this->gs_id = $gs_id;

        return $this;
    }
}
