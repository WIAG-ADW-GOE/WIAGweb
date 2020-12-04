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
     * @ORM\Column(name="id_ref", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $full_citation;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $author_editor;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $onlineressource;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $short_title;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $ri_opac_id;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $isbn;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $gbv;


    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullCitation(): ?string
    {
        return $this->full_citation;
    }

    public function getAuthorEditor(): ?string
    {
        return $this->author_editor;
    }

    public function getOnlineressource(): ?string
    {
        return $this->onlineressource;
    }

    public function getShortTitle(): ?string
    {
        return $this->short_title;
    }

    public function getRiOpacId(): ?string
    {
        return $this->ri_opac_id;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function getGbv(): ?string
    {
        return $this->gbv;
    }


    public function getSort(): ?string
    {
        return $this->sort;
    }

    public function toArray(): array {
        $rfj = array();

        $rfj['title'] = $this->getFullCitation();

        $fv = $this->getAuthorEditor();
        if($fv) $rfj['authorOrEditor'] = $fv;

        $fv = $this->getOnlineressource();
        if($fv) $rfj['online'] = $fv;

        $fv = $this->getShortTitle();
        if($fv) $rfj['shortTitle'] = $fv;

        return $rfj;

    }

}
