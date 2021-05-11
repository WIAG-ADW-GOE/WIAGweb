<?php

namespace App\Entity;

use App\Repository\CnReferenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnReferenceGSRepository::class)
 */
class CnReferenceGS
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=511)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=511, nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $onlineResource;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $shorttitle;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $number_vol;

    /**
     * @ORM\Column(type="string", length=1023, nullable=true)
     */
    private $full_title;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getOnlineResource(): ?string
    {
        return $this->onlineResource;
    }

    public function setOnlineResource(?string $onlineResource): self
    {
        $this->onlineResource = $onlineResource;

        return $this;
    }

    public function getShorttitle(): ?string
    {
        return $this->shorttitle;
    }

    public function setShorttitle(?string $shorttitle): self
    {
        $this->shorttitle = $shorttitle;

        return $this;
    }

    public function getNumberVol(): ?string
    {
        return $this->number_vol;
    }

    public function setNumberVol(?string $number_vol): self
    {
        $this->number_vol = $number_vol;

        return $this;
    }

    public function getFullTitle(): ?string
    {
        return $this->full_title;
    }

    public function setFullTitle(?string $full_title): self
    {
        $this->full_title = $full_title;

        return $this;
    }
}
