<?php

namespace App\Entity;

use App\Repository\CanonGSRepository;
use App\Entity\Person;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cn_canon_gs")
 * @ORM\Entity(repositoryClass=CanonGSRepository::class)
 */
class CanonGS
{
    const WIAGID_PREFIX = 'WIAG-Pers-CANON-';
    const WIAGID_POSTFIX = '-001';
    const WIAGID_EPISC_PREFIX = 'WIAG-Pers-EPISCGatz-';
    const WIAGID_EPISC_POSTFIX = '-001';

    static public function datasource(): string {
        return 'gs';
    }

    static public function extractDbId($id): ?string {
        $db_id = [];
        # at the moment we do not take care about multiple IDs for one person
        $id_prefix = Canon::WIAGID_PREFIX;
        $rgs = "/{$id_prefix}((gs)?[0-9]+)-[0-9]{3}/";
        preg_match($rgs, $id, $db_id);
        if (count($db_id) > 1) {
            return ltrim($db_id[1], "0");
        } else {
            return null;
        }
    }

    /**
     * @ORM\OneToMany(targetEntity="CnOfficeGS", mappedBy="canon")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_canon")
     * @ORM\OrderBy({"location_show" = "ASC", "idMonastery" = "ASC", "numdate_start" = "ASC"})
     */
    private $offices;

    /**
     * @ORM\OneToMany(targetEntity="CnCanonReferenceGS", mappedBy="canon")
     * @ORM\JoinColumn(name="id", referencedColumnName="id_canon")
     * @ORM\OrderBy({"idReference" = "ASC"})
     */
    private $references;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numdateDeath;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numdateBirth;

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
     * @ORM\Column(type="string", length=127, nullable=true)
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dateHistFirst;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dateHistLast;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $wiagEpiscId;

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

    public function setOffices($offices) {
        $this->offices = $offices;
        return $this;
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

    // 2021-09-04 obsolete see extractDbId
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

        public function getNumdateDeath(): ?int
    {
        return $this->numdateDeath;
    }

    public function setNumdateDeath(?int $dateDeath): self
    {
        $this->numdateDeath = $dateDeath;

        return $this;
    }

    public function getNumdateBirth(): ?int
    {
        return $this->numdateBirth;
    }

    public function setNumdateBirth(?int $dateBirth): self
    {
        $this->numdateBirth = $dateBirth;

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

    public function getDateHistFirst(): ?int
    {
        return $this->dateHistFirst;
    }

    public function setDateHistFirst(?int $dateHistFirst): self
    {
        $this->dateHistFirst = $dateHistFirst;

        return $this;
    }

    public function getDateHistLast(): ?int
    {
        return $this->dateHistLast;
    }

    public function setDateHistLast(?int $dateHistLast): self
    {
        $this->dateHistLast = $dateHistLast;

        return $this;
    }

    public function getWiagEpiscId(): ?string
    {
        return $this->wiagEpiscId;
    }

    public function getWiagEpiscIdLong(): ?string {
        if (is_null($this->wiagEpiscId)) {
            return null;
        }
        if (str_contains($this->wiagEpiscId, self::WIAGID_EPISC_PREFIX)) {
            return $this->wiagEpiscId;
        }
        $id_padded = str_pad($this->wiagEpiscId, 5, '0', STR_PAD_LEFT);
        return self::WIAGID_EPISC_PREFIX.$id_padded.self::WIAGID_EPISC_POSTFIX;
    }

    public function setWiagEpiscId(?int $wiagEpiscId): self
    {
        $this->wiagEpiscId = $wiagEpiscId;

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

    /**
     * concatenate name variants and name comment
     */
    public function createNameVariantLine() {
        $lineElts = array();
        $eltCands = [
            $this->givennameVariant,
            $this->familynameVariant,
            $this->commentName,
        ];
        foreach ($eltCands as $elt) {
            if (!is_null($elt) && $elt != '') {
                $lineElts[] = $elt;
            }
        }

        $commentLine = null;
        if (count($lineElts) > 0) {
            $commentLine = implode('; ', $lineElts);
        }

        return $commentLine;
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

    public function getFlagNameVariant() {
        return ($this->givennameVariant and
                $this->givennameVariant != ''
                or $this->familynameVariant and
                $this->familynameVariant != ''
                or $this->commentName and
                $this->commentName != '');
    }

    public function getFlagCommentPerson() {
        return ($this->commentPerson and
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

    public function copyExternalIds(Person $person): self {
        if (is_null($person)) {
            return $this;
        }
        $this->gsnId = $person->getGsId();
        $this->gndId = $person->getGndId();
        $this->viafId = $person->getViafId();
        $this->wikidataId = $person->getWikidataId();
        $this->wikipediaUrl = $person->getWikipediaurl();

        return $this;
    }

}
