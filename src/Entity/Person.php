<?php
// src/Entity/Person.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=63, nullable = false)
     */
    private $wiagid;
    
    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private $familyname;

    /**
     * @ORM\Column(type="string", length=255, nullable = false)
     */
    private $givenname;

    /**
     * @ORM\Column(type="string", length=31, nullable = true)
     */
    private $prefix;
    
    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $date_birth;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $date_death;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $gsid;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $gndid;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $viafid;

    /**
     * @ORM\Column(type="string", length=63, nullable = true)
     */
    private $wikipediaurl;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWiagid(): ?string
    {
        return $this->wiagid;
    }

    public function setWiagid(string $wiagid): self
    {
        $this->wiagid = $wiagid;

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

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getDate_birth(): ?string
    {
        return $this->date_birth;
    }

    public function setDate_birth(?string $date_birth): self
    {
        $this->date_birth = $date_birth;

        return $this;
    }

    public function getDate_death(): ?string
    {
        return $this->date_death;
    }

    public function setDate_death(?string $date_death): self
    {
        $this->date_death = $date_death;

        return $this;
    }

    public function getGsid(): ?string
    {
        return $this->gsid;
    }

    public function setGsid(?string $gsid): self
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

    public function getWikipediaurl(): ?string
    {
        return $this->wikipediaurl;
    }

    public function setWikipediaurl(?string $url_wikipedia): self
    {
        $this->wikipediaurl = $wikipediaurl;

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
    
}
