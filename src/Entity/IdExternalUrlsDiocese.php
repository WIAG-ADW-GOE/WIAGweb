<?php

namespace App\Entity;

use App\Repository\IdExternalUrlsDioceseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IdExternalUrlsDioceseRepository::class)
 * @ORM\Table(name="id_external_urls_diocese")
 */
class IdExternalUrlsDiocese
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id_external_url_diocese;

    /**
     * @ORM\Column(type="integer")
     */
    private $diocese_id;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $url_value;

    /**
     * @ORM\Column(type="integer")
     */
    private $url_type_id;

    public function getIdExternalUrlDiocese(): ?int
    {
        return $this->id_external_url_diocese;
    }

    public function setIdExternalUrlDiocese(int $id_external_url_diocese): self
    {
        $this->id_external_url_diocese = $id_external_url_diocese;

        return $this;
    }

    public function getDioceseId(): ?int
    {
        return $this->diocese_id;
    }

    public function setDioceseId(int $diocese_id): self
    {
        $this->diocese_id = $diocese_id;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getUrlValue(): ?string
    {
        return $this->url_value;
    }

    public function setUrlValue(string $url_value): self
    {
        $this->url_value = $url_value;

        return $this;
    }

    public function getUrlTypeId(): ?int
    {
        return $this->url_type_id;
    }

    public function setUrlTypeId(int $url_type_id): self
    {
        $this->url_type_id = $url_type_id;

        return $this;
    }
}
