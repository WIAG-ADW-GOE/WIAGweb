<?php

namespace App\Entity;

use App\Repository\AltLabelDioceseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AltLabelDioceseRepository::class)
 * @ORM\Table(name="alt_label_diocese")
 */
class AltLabelDiocese
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id_label_diocese;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $alt_label_diocese;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $lang;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $diocese_id;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $ressource;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdLabelDiocese(): ?int
    {
        return $this->id_label_diocese;
    }

    public function setIdLabelDiocese(int $id_label_diocese): self
    {
        $this->id_label_diocese = $id_label_diocese;

        return $this;
    }

    public function getAltLabelDiocese(): ?string
    {
        return $this->alt_label_diocese;
    }

    public function setAltLabelDiocese(?string $alt_label_diocese): self
    {
        $this->alt_label_diocese = $alt_label_diocese;

        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): self
    {
        $this->lang = $lang;

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

    public function getRessource(): ?string
    {
        return $this->ressource;
    }

    public function setRessource(?string $ressource): self
    {
        $this->ressource = $ressource;

        return $this;
    }
}
