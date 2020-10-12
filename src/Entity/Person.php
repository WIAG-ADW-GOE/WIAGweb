<?php
// src/Entity/Person.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person {
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
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $familyname_variant;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $givenname_variant;

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
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=511, nullable=true)
     */
    private $comment_person;


    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $comment_name;

    /**
     * not mapped to the database
     */
    private $wiagpathid;

    /**
     * not mapped to the database
     */
    private $offices;

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

    public function getFamilynameVariant(): ?string
    {
        return $this->familyname_variant;
    }
    
    public function setFamilynameVariant(?string $familyname_variant): self
    {
        $this->familyname_variant = $familyname_variant;

        return $this;
    }

    public function getGivennameVariant(): ?string
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
        return $this->wikipediaurl;
    }

    public function setWikipediaurl(?string $wikipediaurl): self
    {
        $this->wikipediaurl = $wikipediaurl;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

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

    
    public function getWiagpathid(): ?string
    {
        return $this->wiagpathid;
    }

    public function setwiagpathid(?string $wiagpathid): self
    {
        $this->wiagpathid = $wiagpathid;

        return $this;
    }

    public function getOffices(): ?array {
        return $this->offices;
    }

    public function setOffices($offices) {
        $this->offices = $offices;

        return $this;
    }

    public function hasNormdata() {
        return ($this->gsid
                || $this->viafid
                || $this->wikipediaurl
                || $this->wikidataid
                || $this->gndid);                        
    }

}
