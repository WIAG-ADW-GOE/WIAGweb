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
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id_domstift;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $name;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $gsId;

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
        return $this->gsId;
    }

    public function setGsId(int $id): self
    {
        $this->gsId = $Id;

        return $this;
    }
}
