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
    private $cnonline;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $id_online;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $authority_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdOnline(): ?string
    {
        return $this->id_online;
    }

    public function setIdOnline(string $id_online): self
    {
        $this->id_online = $id_online;

        return $this;
    }

    public function getAuthorityId(): ?string
    {
        return $this->authority_id;
    }

    public function setAuthorityId(string $authority_id): self
    {
        $this->authority_id = $authority_id;

        return $this;
    }
}
