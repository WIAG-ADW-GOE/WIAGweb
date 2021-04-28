<?php

namespace App\Entity;

use App\Repository\CnCanonReferenceGSRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CnCanonReferenceGSRepository::class)
 */
class CnCanonReferenceGS
{

    /**
     * @ORM\ManyToOne(targetEntity="CanonGS", inversedBy="references")
     * @ORM\JoinColumn(name="id_canon", referencedColumnName="id")
     */
    private $canon;

    /**
     * @ORM\ManyToOne(targetEntity="CnReference")
     * @ORM\JoinColumn(name="id_reference", referencedColumnName="id")
     */
    private $reference;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $idCanon;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $idReference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $itemReference;

    /**
     * @ORM\Column(type="string", length=511, nullable=true)
     */
    private $pageReference;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isbio;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $idInReference;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCanon(): ?string
    {
        return $this->idCanon;
    }

    public function setIdCanon(string $idCanon): self
    {
        $this->idCanon = $idCanon;

        return $this;
    }

    public function getIdReference(): ?int
    {
        return $this->idReference;
    }

    public function setIdReference(int $idReference): self
    {
        $this->idReference = $idReference;

        return $this;
    }

    public function getItemReference(): ?string
    {
        return $this->itemReference;
    }

    public function setItemReference(?string $itemReference): self
    {
        $this->itemReference = $itemReference;

        return $this;
    }

    public function getPageReference(): ?string
    {
        return $this->pageReference;
    }

    public function setPageReference(?string $pageReference): self
    {
        $this->pageReference = $pageReference;

        return $this;
    }

    public function getReference(): ?object
    {
        return $this->reference;
    }

    public function getIsbio(): ?bool
    {
        return $this->isbio;
    }

    public function setIsbio(bool $isbio): self
    {
        $this->isbio = $isbio;

        return $this;
    }

    public function getPageBio(): ?string {
        if (!$this->isbio) {
            return null;
        }
        $matches = [];
        preg_match("~<b>([0-9]+)~", $this->pageReference, $matches);
        if (count($matches) < 2) {
            return null;
        } else {
            return $matches[1];
        }
    }

    public function getIdInReference(): ?string
    {
        return $this->idInReference;
    }

    public function setIdInReference(?string $idInReference): self
    {
        $this->idInReference = $idInReference;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * check if a reference contains a biogram
     * return list of pages
     */
    public function getPages() {
        $s = $this->pageReference;
        if (is_null($s)) {
            return array();
        }
        $cs = explode(',', $s);
        $cs = array_map('trim', $cs);

        $cpages = [];
        $matches = [];
        foreach ($cs as $es) {
            $matches = [];
            preg_match("~<b>(.*)</b>~", $es, $matches);
            $isbio = count($matches) > 1;
            $page = $isbio ? $matches[1] : $es;
            $cpages[] = [
                'page' => $page,
                'isbio' => $isbio,
            ];
        }

        return $cpages;
    }


}
