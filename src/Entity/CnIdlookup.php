<?php

namespace App\Entity;

use App\Repository\CnIdlookupRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnIdlookupRepository::class)
 */
class CnIdlookup
{

    /**
     * @ORM\ManyToOne(targetEntity="CnOnline", inversedBy="idlookup")
     * @ORM\JoinColumn(name="id_online", referencedColumnName="id")
     */
    private $cnOnline;

    public function setCnOnline(CnOnline $co): self {
        $this->cnOnline = $co;
        return $this;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $idOnline;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $authorityId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdOnline(): ?string
    {
        return $this->idOnline;
    }

    public function setIdOnline(string $id): self
    {
        $this->idOnline = $id;

        return $this;
    }

    public function getAuthorityId(): ?string
    {
        return $this->authorityId;
    }

    public function setAuthorityId(string $id): self
    {
        $this->authorityId = $id;

        return $this;
    }
}
