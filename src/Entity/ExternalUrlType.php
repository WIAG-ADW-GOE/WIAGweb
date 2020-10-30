<?php

namespace App\Entity;

use App\Repository\ExternalUrlTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExternalUrlTypeRepository::class)
 * @ORM\Table(name="external_url_type")
 */
class ExternalUrlType
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id_url_type;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $url_type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url_name_formatter;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url_value_example;

    /**
     * @ORM\Column(type="integer", length=31, nullable=true)
     */
    private $display_order;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url_formatter;

    public function getIdUrlType(): ?int
    {
        return $this->id_url_type;
    }

    public function setIdUrlType(int $id_url_type): self
    {
        $this->id_url_type = $id_url_type;

        return $this;
    }

    public function getUrlType(): ?string
    {
        return $this->url_type;
    }

    public function setUrlType(string $url_type): self
    {
        $this->url_type = $url_type;

        return $this;
    }

    public function getUrlNameFormatter(): ?string
    {
        return $this->url_name_formatter;
    }

    public function setUrlNameFormatter(?string $url_name_formatter): self
    {
        $this->url_name_formatter = $url_name_formatter;

        return $this;
    }

    public function getUrlValueExample(): ?string
    {
        return $this->url_value_example;
    }

    public function setUrlValueExample(?string $url_value_example): self
    {
        $this->url_value_example = $url_value_example;

        return $this;
    }

    public function getDisplayOrder(): ?string
    {
        return $this->display_order;
    }

    public function setDisplayOrder(?string $display_order): self
    {
        $this->display_order = $display_order;

        return $this;
    }

    public function getUrlFormatter(): ?string
    {
        return $this->url_formatter;
    }

    public function setUrlFormatter(string $url_formatter): self
    {
        $this->url_formatter = $url_formatter;

        return $this;
    }
}
