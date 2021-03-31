<?php

namespace App\Entity;

use App\Repository\CnReferenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnReferenceRepository::class)
 */
class CnReference
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="string", length=31)
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
}
