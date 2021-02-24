<?php

namespace App\Entity;

use App\Repository\CanonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cn_canon")
 * @ORM\Entity(repositoryClass=CanonRepository::class)
 */
class Canon
{
    /**
     * @ORM\OneToOne(targetEntity="CnEra", mappedBy="id_canon")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_canon")
     */
    # private $era;
    

    /**
     * @ORM\OneToMany(targetEntity="CnOffice", mappedBy="id_canon")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_canon")
     */
    # private $offices;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="string", length=63)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $itemReference;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $pageReference;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idReference;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $prefixName;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $givenname;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $familyname;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $dateDeath;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $dateBirth;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $religiousOrder;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $urlWikipedia;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isready;

    /**
     * @ORM\Column(type="string", length=1023, nullable=true)
     */
    private $annotationEd;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $idInReference;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $diocese;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $academicTitle;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $idGnd;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $idGsn;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $idViaf;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $idWikidata;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $authorOrig;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $familynameVariant;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $givennameVariant;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $commentName;

    /**
     * @ORM\Column(type="string", length=1023, nullable=true)
     */
    private $commentPerson;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $dateHistFirst;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $dateHistLast;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idWiagEpisc;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sameAs;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mergedInto;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdReference(): ?int
    {
        return $this->idReference;
    }

    public function setIdReference(?int $idReference): self
    {
        $this->idReference = $idReference;

        return $this;
    }

    public function getPrefixName(): ?string
    {
        return $this->prefixName;
    }

    public function setPrefixName(?string $prefixName): self
    {
        $this->prefixName = $prefixName;

        return $this;
    }

    public function getGivenname(): ?string
    {
        return $this->givenname;
    }

    public function setGivenname(string $givenname): self
    {
        $this->givenname = $givenname;

        return $this;
    }

    public function getFamilyname(): ?string
    {
        return $this->familyname;
    }

    public function setFamilyname(?string $familyname): self
    {
        $this->familyname = $familyname;

        return $this;
    }

    public function getDateDeath(): ?string
    {
        return $this->dateDeath;
    }

    public function setDateDeath(?string $dateDeath): self
    {
        $this->dateDeath = $dateDeath;

        return $this;
    }

    public function getDateBirth(): ?string
    {
        return $this->dateBirth;
    }

    public function setDateBirth(?string $dateBirth): self
    {
        $this->dateBirth = $dateBirth;

        return $this;
    }

    public function getReligiousOrder(): ?string
    {
        return $this->religiousOrder;
    }

    public function setReligiousOrder(?string $religiousOrder): self
    {
        $this->religiousOrder = $religiousOrder;

        return $this;
    }

    public function getUrlWikipedia(): ?string
    {
        return $this->urlWikipedia;
    }

    public function setUrlWikipedia(?string $urlWikipedia): self
    {
        $this->urlWikipedia = $urlWikipedia;

        return $this;
    }

    public function getIsready(): ?bool
    {
        return $this->isready;
    }

    public function setIsready(?bool $isready): self
    {
        $this->isready = $isready;

        return $this;
    }

    public function getAnnotationEd(): ?string
    {
        return $this->annotationEd;
    }

    public function setAnnotationEd(?string $annotationEd): self
    {
        $this->annotationEd = $annotationEd;

        return $this;
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

    public function getDiocese(): ?string
    {
        return $this->diocese;
    }

    public function setDiocese(?string $diocese): self
    {
        $this->diocese = $diocese;

        return $this;
    }

    public function getAcademicTitle(): ?string
    {
        return $this->academicTitle;
    }

    public function setAcademicTitle(?string $academicTitle): self
    {
        $this->academicTitle = $academicTitle;

        return $this;
    }

    public function getIdGnd(): ?string
    {
        return $this->idGnd;
    }

    public function setIdGnd(?string $idGnd): self
    {
        $this->idGnd = $idGnd;

        return $this;
    }

    public function getIdGsn(): ?string
    {
        return $this->idGsn;
    }

    public function setIdGsn(string $idGsn): self
    {
        $this->idGsn = $idGsn;

        return $this;
    }

    public function getIdViaf(): ?string
    {
        return $this->idViaf;
    }

    public function setIdViaf(?string $idViaf): self
    {
        $this->idViaf = $idViaf;

        return $this;
    }

    public function getIdWikidata(): ?string
    {
        return $this->idWikidata;
    }

    public function setIdWikidata(?string $idWikidata): self
    {
        $this->idWikidata = $idWikidata;

        return $this;
    }

    public function getAuthorOrig(): ?string
    {
        return $this->authorOrig;
    }

    public function setAuthorOrig(?string $authorOrig): self
    {
        $this->authorOrig = $authorOrig;

        return $this;
    }

    public function getFamilynameVariant(): ?string
    {
        return $this->familynameVariant;
    }

    public function setFamilynameVariant(?string $familynameVariant): self
    {
        $this->familynameVariant = $familynameVariant;

        return $this;
    }

    public function getGivennameVariant(): ?string
    {
        return $this->givennameVariant;
    }

    public function setGivennameVariant(?string $givennameVariant): self
    {
        $this->givennameVariant = $givennameVariant;

        return $this;
    }

    public function getCommentName(): ?string
    {
        return $this->commentName;
    }

    public function setCommentName(?string $commentName): self
    {
        $this->commentName = $commentName;

        return $this;
    }

    public function getCommentPerson(): ?string
    {
        return $this->commentPerson;
    }

    public function setCommentPerson(?string $commentPerson): self
    {
        $this->commentPerson = $commentPerson;

        return $this;
    }

    public function getDateHistFirst(): ?string
    {
        return $this->dateHistFirst;
    }

    public function setDateHistFirst(?string $dateHistFirst): self
    {
        $this->dateHistFirst = $dateHistFirst;

        return $this;
    }

    public function getDateHistLast(): ?string
    {
        return $this->dateHistLast;
    }

    public function setDateHistLast(?string $dateHistLast): self
    {
        $this->dateHistLast = $dateHistLast;

        return $this;
    }

    public function getIdWiagEpisc(): ?int
    {
        return $this->idWiagEpisc;
    }

    public function setIdWiagEpisc(?int $idWiagEpisc): self
    {
        $this->idWiagEpisc = $idWiagEpisc;

        return $this;
    }

    public function getSameAs(): ?int
    {
        return $this->sameAs;
    }

    public function setSameAs(?int $sameAs): self
    {
        $this->sameAs = $sameAs;

        return $this;
    }

    public function getMergedInto(): ?int
    {
        return $this->mergedInto;
    }

    public function setMergedInto(?int $mergedInto): self
    {
        $this->mergedInto = $mergedInto;

        return $this;
    }
}
