<?php

namespace App\Entity;

use App\Repository\CanonRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cn_canon")
 * @ORM\Entity(repositoryClass=CanonRepository::class)
 */
class Canon
{
    const WIAGID_PREFIX = 'WIAG-Pers-CANON-';
    const WIAGID_POSTFIX = '-001';

    /**
     * @ORM\OneToMany(targetEntity="CnNamelookup", mappedBy="canon")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_canon")
     */
    private $namelookup;

    /**
     * @ORM\OneToOne(targetEntity="CnEra", mappedBy="canon")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_canon")
     */
    private $era;

    /**
     * @ORM\OneToMany(targetEntity="CnOffice", mappedBy="canon")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_canon")
     */
    private $offices;

    /**
     * @ORM\OneToMany(targetEntity=CnOfficeSortkey::class, mappedBy="canon")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_canon")
     */
    private $officeSortkeys;

    /**
     * @ORM\OneToMany(targetEntity="CnCanonReference", mappedBy="canon")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_canon")
     */
    private $references;

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
    private $wikipediaUrl;

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

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $gsnId;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $gndId;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $viafId;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $wikidataId;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $status;

    public function __construct() {
        $this->officeSortkeys = new ArrayCollection();
    }


    public function getNamelookup() {
        return $this->namelookup;
    }

    public function getEra() {
        return $this->era;
    }

    public function getOffices() {
        return $this->offices;
    }

    public function getReferences() {
        return $this->references;
    }

    /**
     * @return Collection|OfficeSortkey[]
     */
    public function getOfficeSortkeys(): Collection {
        return $this->officeSortkeys;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getWiagidLong(): ?string
    {
        $id_padded = str_pad($this->id, 5, '0', STR_PAD_LEFT);
        return self::WIAGID_PREFIX.$id_padded.self::WIAGID_POSTFIX;
    }

    public static function isIdCanon(string $id) {
        $headlen = strlen(self::WIAGID_PREFIX);
        $head = substr($id, 0, $headlen);
        return $head == self::WIAGID_PREFIX;
    }

    public static function shortId(?string $id) {
        if (is_null($id)) return $id;
        if (strpos($id, self::WIAGID_PREFIX) === false) {
            return ltrim($id, "0");
        }
        $head = strlen(self::WIAGID_PREFIX);
        $tail = strlen(self::WIAGID_POSTFIX);
        $paddedId = substr($id, $head, -$tail);
        $shortId = ltrim($paddedId, "0");
        return $shortId;
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

    public function getWikipediaUrl(): ?string
    {
        return $this->wikipediaUrl;
    }

    public function setWikipedia(?string $wikipediaUrl): self
    {
        $this->wikipediaUrl = $wikipediaUrl;
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

    /**
     * get and merge ids in references
     * source Canon and CnOffice
     */
    public function getIdInReference(): ?string
    {
        // we can not use array_column here
        $refidsoc = array();
        foreach ($this->offices as $oc) {
            $refidoc = $oc->getIdInReference();
            if (!is_null($refidoc)) {
                $refidsoc[] = $refidoc;
            }
        }
        $refids = array_merge([$this->idInReference], $refidsoc);
        $refidstr = count($refids) > 0 ? $refidstr = implode(", ", $refids) : null;
        return $refidstr;
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

    public function hasMonastery(): bool {
        foreach($this->offices as $oc) {
            if($oc->getIdMonastery()) return true;
        }
        return false;
    }

    public function getDisplayname() {
        $prefixpart = strlen($this->prefixName) > 0 ? ' '.$this->prefixName : '';
        $familypart = strlen($this->familyname) > 0 ? ' '.$this->familyname : '';
        return $this->givenname.$prefixpart.$familypart;
    }

    public function getWikipediaTitle(): ?string {
        $url = $this->getWikipediaurl();
        if(!$url || $url == '') return null;

        $wikipediaurlbase = 'https://de.wikipedia.org/wiki/';


        $head = strlen($wikipediaurlbase);
        $wikipediatitle = substr($url, $head);
        $wikipediatitle = urldecode($wikipediatitle);
        $wikipediatitle = str_replace('_', ' ', $wikipediatitle);

        return $wikipediatitle;
    }

    public function hasExternalIdentifier() {
        return ($this->viafId
                || $this->wikidataId
                || $this->gndId);
    }

    public function hasOtherIdentifier() {
        return ($this->gsnId
                || $this->wikipediaUrl);
    }

    public function getFlagComment() {
        return ($this->givennameVariant and
                $this->givennameVariant != ''
                or $this->familynameVariant and
                $this->familynameVariant != ''
                or $this->commentName and
                $this->commentName != ''
                or $this->commentPerson and
                $this->commentPerson != '');
    }

    public function getGsnId(): ?string
    {
        return $this->gsnId;
    }

    public function setGsnId(?string $gsnId): self
    {
        $this->gsnId = $gsnId;

        return $this;
    }

    public function getGndId(): ?string
    {
        return $this->gndId;
    }

    public function setGndId(?string $gndId): self
    {
        $this->gndId = $gndId;

        return $this;
    }

    public function getViafId(): ?string
    {
        return $this->viafId;
    }

    public function setViafId(?string $viafId): self
    {
        $this->viafId = $viafId;

        return $this;
    }

    public function getWikidataId(): ?string
    {
        return $this->wikidataId;
    }

    public function setWikidataId(?string $wikidataId): self
    {
        $this->wikidataId = $wikidataId;

        return $this;
    }

    public static function isWiagidLong($wiagidlong) {
        // do something reasonable as soon as the WIAG ID format is defined
        return false;
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



}
