<?php

namespace App\Entity;

use App\Repository\FamilynamevariantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FamilynamevariantRepository::class)
 */
class Familynamevariant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="familyname_variant")
     * @ORM\JoinColumn(name="wiagid", referencedColumnName="wiagid")
     */
    private $wiagid;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $familyname;

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

    public function getFamilyname(): ?string
    {
        return $this->familyname;
    }

    public function setFamilyname(?string $familyname): self
    {
        $this->familyname = $familyname;

        return $this;
    }
}
