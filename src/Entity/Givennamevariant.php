<?php

namespace App\Entity;

use App\Repository\GivennamevariantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GivennamevariantRepository::class)
 */
class Givennamevariant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="givenname_variant")
     * @ORM\JoinColumn(name="wiagid", referencedColumnName="wiagid")
     */
    private $wiagid;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $givenname;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGivenname(): ?string
    {
        return $this->givenname;
    }

    public function setGivenname(?string $givenname): self
    {
        $this->givenname = $givenname;

        return $this;
    }
}
