<?php

namespace App\Entity;

use App\Repository\ReferenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReferenceRepository::class)
 */
class Reference
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $reference_id;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $onlineressource;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $short;

    public function getReferenceId(): ?int
    {
        return $this->reference_id;
    }

    public function setReferenceId(int $reference_id): self
    {
        $this->reference_id = $reference_id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getOnlineressource(): ?string
    {
        return $this->onlineressource;
    }

    public function setOnlineressource(?string $onlineressource): self
    {
        $this->onlineressource = $onlineressource;

        return $this;
    }

    public function getShort(): ?string
    {
        return $this->short;
    }

    public function setShort(string $short): self
    {
        $this->short = $short;

        return $this;
    }

}
