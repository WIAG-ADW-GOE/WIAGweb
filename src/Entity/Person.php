<?php
// src/Entity/Person.php
namespace App\Entity;

use App\Entity\Office;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person {
    const WIAGID_PREFIX = 'WIAG-Pers-EPISCGatz-';
    const WIAGID_POSTFIX = '-001';

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=63, nullable = false)
     */
    private $wiagid;

    /**
     * @ORM\Column(type="string", length=511, nullable=true)
     */
    private $ri_opac;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $authors_gatz;

    /**
     * @ORM\Column(type="string", length=41, nullable=true)
     */
    private $pages_gatz;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $prefix_name;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $familyname;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $givenname;


    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $date_birth;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $date_death;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $religious_order;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $gsid;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $gndid;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $viafid;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $wikidataid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $wikipediaurl;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reference_id;

    /**
     * @ORM\Column(type="string", length=511, nullable=true)
     */
    private $comment_person;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $comment_name;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $familyname_variant;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $givenname_variant;

    /**
     * @ORM\OneToMany(targetEntity="Namelookup", mappedBy="wiagid_person")
     * @ORM\JoinColumn(name="wiagid", referencedColumnName="wiagid_person")
     */
    private $namelookup;

    /**
     * @ORM\OneToOne(targetEntity="Era", mappedBy="wiagid_person")
     * @ORM\JoinColumn(name="wiagid", referencedColumnName="wiagid_person")
     */
    private $era;

    /**
     * @ORM\ManyToOne(targetEntity="Reference")
     * @ORM\JoinColumn(name="reference_id", referencedColumnName="reference_id")
     */
    private $reference;


    /**
     * @ORM\OneToMany(targetEntity="Office", mappedBy="wiagid_person")
     * @ORM\JoinColumn(name="wiagid", referencedColumnName="wiagid_person")
     */
    private $offices;

    public static function isWiagidLong($wiagidlong) {
        $match = stristr($wiagidlong, self::WIAGID_PREFIX);
        return strlen($match) > 0;
    }

    public static function wiagidLongToWiagid($wiagidlong) {
        if(strpos($wiagidlong, self::WIAGID_PREFIX) === false)
            return $wiagidlong;
        $head = strlen(self::WIAGID_PREFIX);
        $tail = strlen(self::WIAGID_POSTFIX);
        return substr($wiagidlong, $head, -$tail);
    }

    public function setFields_obsolete($p) {
        $this->wiagid = $p['wiagid'];
        $this->authors_gatz = $p['authors_gatz'];
        $this->pages_gatz = $p['pages_gatz'];
        $this->familyname = $p['familyname'];
        $this->givenname = $p['givenname'];
        $this->prefix_name = $p['prefix_name'];
        $this->date_birth = $p['date_birth'];
        $this->date_death = $p['date_death'];
        $this->religious_order = $p['religious_order'];
        $this->gsid = $p['gsid'];
        $this->gndid = $p['gndid'];
        $this->wikidataid = $p['wikidataid'];
        $this->wikipediaurl = $p['wikipediaurl'];

        return $this;
    }

    public function getWiagid(): ?string
    {
        return $this->wiagid;
    }

    public function getRiOpac(): ?string
    {
        return $this->ri_opac;
    }

    public function setRiOpac(?string $ri_opac): self
    {
        $this->ri_opac = $ri_opac;

        return $this;
    }

    public function getAuthorsGatz(): ?string
    {
        return $this->authors_gatz;
    }

    public function setAuthorsGatz(?string $authors_gatz): self
    {
        $this->authors_gatz = $authors_gatz;

        return $this;
    }

    public function getPagesGatz(): ?string
    {
        return $this->pages_gatz;
    }

    public function setPagesGatz(?string $pages_gatz): self
    {
        $this->pages_gatz = $pages_gatz;

        return $this;
    }

    public function getPrefixName(): ?string
    {
        return $this->prefix_name;
    }

    public function setPrefixName(?string $prefix_name): self
    {
        $this->prefix_name = $prefix_name;

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

    public function getGivenname(): ?string
    {
        return $this->givenname;
    }

    public function setGivenname(string $givenname): self
    {
        $this->givenname = $givenname;

        return $this;
    }

    public function getFamilynameVariant()
    {
        return $this->familyname_variant;
    }

    public function setFamilynameVariant(?string $familyname_variant): self
    {
        $this->familyname_variant = $familyname_variant;

        return $this;
    }

    public function getGivennameVariant()
    {
        return $this->givenname_variant;
    }


    public function setGivennameVariant(?string $givenname_variant): self
    {
        $this->givenname_variant = $givenname_variant;

        return $this;
    }

    public function getDateBirth(): ?string
    {
        return $this->date_birth;
    }

    public function setDateBirth(?string $date_birth): self
    {
        $this->date_birth = $date_birth;

        return $this;
    }

    public function getDateDeath(): ?string
    {
        return $this->date_death;
    }

    public function setDateDeath(?string $date_death): self
    {
        $this->date_death = $date_death;

        return $this;
    }

    public function getReligiousOrder(): ?string
    {
        return $this->religious_order;
    }

    public function setReligiousOrder(?string $religious_order): self
    {
        $this->religious_order = $religious_order;

        return $this;
    }

    public function getGsid(): ?string
    {
        return $this->gsid;
    }

    public function setGsid(string $gsid): self
    {
        $this->gsid = $gsid;

        return $this;
    }

    public function getGndid(): ?string
    {
        return $this->gndid;
    }

    public function setGndid(?string $gndid): self
    {
        $this->gndid = $gndid;

        return $this;
    }

    public function getViafid(): ?string
    {
        return $this->viafid;
    }

    public function setViafid(?string $viafid): self
    {
        $this->viafid = $viafid;

        return $this;
    }

    public function getWikidataid(): ?string
    {
        return $this->wikidataid;
    }

    public function setWikidataid(?string $wikidataid): self
    {
        $this->wikidataid = $wikidataid;

        return $this;
    }

    public function getWikipediaurl(): ?string
    {
        return trim($this->wikipediaurl, " \t\n#");
    }

    public function setWikipediaurl(?string $wikipediaurl): self
    {
        $this->wikipediaurl = $wikipediaurl;

        return $this;
    }

    public function getReferenceId(): ?string
    {
        return $this->reference_id;
    }

    public function setReferenceId(?string $reference): self
    {
        $this->reference = $reference_id;

        return $this;
    }

    public function getCommentPerson(): ?string
    {
        return $this->comment_person;
    }

    public function setCommentPerson(?string $comment_person): self
    {
        $this->comment_person = $comment_person;

        return $this;
    }

    public function getCommentName(): ?string
    {
        return $this->comment_name;
    }

    public function setCommentName(?string $comment_name): self
    {
        $this->comment_name = $comment_name;

        return $this;
    }

    public function getWiagidLong(): ?string
    {
        return self::WIAGID_PREFIX.$this->wiagid.self::WIAGID_POSTFIX;
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

    public function getOffices() {
        return $this->offices;
    }

    public function setOffices($offices) {
        $this->offices = $offices;

        return $this;
    }

    public function getEra() {
        return $this->era;
    }

    public function setEra($era) {
        $this->era = $era;

        return $this;
    }

    public function getNamelookup() {
        return $this->namelookup;
    }

    public function setNamelookup($namelookup) {
        $this->namelookup = $namelookup;

        return $this;
    }

    public function getReference() {
        return $this->reference;
    }

    public function getDisplayname() {
        $prefixpart = strlen($this->prefix_name) > 0 ? ' '.$this->prefix_name : '';
        $familypart = strlen($this->familyname) > 0 ? ' '.$this->familyname : '';
        return $this->givenname.$prefixpart.$familypart;
    }


    public function hasExternalIdentifier() {
        return ($this->viafid
                || $this->wikidataid
                || $this->gndid);
    }

    public function hasOtherIdentifier() {
        return ($this->gsid
                || $this->wikipediaurl);
    }

    public function getFlagComment() {
        return ($this->givenname_variant and
                $this->givenname_variant != ''
                or $this->familyname_variant and
                $this->familyname_variant != ''
                or $this->comment_name and
                $this->comment_name != ''
                or $this->comment_person and
                $this->comment_person != '');
    }

    public function toArray() {
        $pj = array();
        $pj['wiagId'] = $this->getWiagidLong();

        $fv = $this->getFamilyname();
        if($fv) $pj['familyName'] = $fv;

        $pj['givenName'] = $this->getGivenname();

        $fv = $this->getPrefixName();
        if($fv) $pj['prefix'] = $fv;

        $fv = $this->getFamilynameVariant();
        if($fv) $pj['variantFamilyName'] = $fv;

        $fv = $this->getGivennameVariant();
        if($fv) $pj['variantGivenName'] = $fv;

        $fv = $this->comment_name;
        if($fv) $pj['comment_name'] = $fv;

        $fv = $this->comment_person;
        if($fv) $pj['comment_person'] = $fv;

        $fv = $this->getGivennameVariant();
        if($fv) $pj['variantGivenName'] = $fv;


        $fv = $this->getDateBirth();
        if($fv) $pj['dateOfBirth'] = $fv;

        $fv = $this->getDateDeath();
        if($fv) $pj['dateOfDeath'] = $fv;

        $fv = $this->getReligiousOrder();
        if($fv) $pj['religiousOrder'] = $fv;

        if($this->hasExternalIdentifier() || $this->hasOtherIdentifier()) {
            $pj['identifier'] = array();
            $nd = &$pj['identifier'];
            $fv = $this->getGsid();
            if($fv) $nd['gsId'] = $fv;

            $fv = $this->getGndid();
            if($fv) $nd['gndId'] = $fv;

            $fv = $this->getViafid();
            if($fv) $nd['viafId'] = $fv;

            $fv = $this->getWikidataid();
            if($fv) $nd['wikidataId'] = $fv;

            $fv = $this->getWikipediaurl();
            if($fv) $nd['wikipediaUrl'] = $fv;
        }

        $offices = $this->getOffices();
        if($offices) {
            $pj['offices'] = array();
            $ocJSON = &$pj['offices'];
            foreach($offices as $oc) {
                $ocJSON[] = $oc->toArray();
            }
        }

        $fv = $this->getReference();
        if($fv) {
            $pj['reference'] = $fv->toArray();
            $fiv = $this->getPagesGatz();
            if($fiv)
                $pj['reference']['pages'] = $fiv;
        }

        return $pj;
    }

    public function hasMonastery(): bool {
        foreach($this->offices as $oc) {
            if($oc->getIdMonastery()) return true;
        }
        return false;
    }


}
