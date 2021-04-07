<?php

namespace App\Entity;

use App\Repository\MonasteryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MonasteryRepository::class)
 */
class Monastery {

    const IDS_DOMSTIFTE = [3498,  3492,   701,   343, 60130,  3493,  3496,   783,
                           675,   739,   832,  3503,  3499,   736,   676,  3488,
                           3501,   792,   794,   616,   628,  3491,   803,   953,
                           3495,   226,  3494,   679,  2066,  3489,  3500,  3487,
                           3490,  3502];
    
    static public function trimDomstift($name): ?string {
        $prefix = 'Domstift';
        $name = ltrim($name);
        $flag = strpos($name, $prefix);
        if ($flag !== false and $flag == 0) {
            return ltrim(substr($name, strlen($prefix)));
        } else {
            return $name;
        }
    }
    
    /**
     * @ORM\Column(type="integer")
     */
    private $id_monastery;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=63, nullable=false)
     */
    private $wiagid;

    /**
     * @ORM\Column(type="date")
     */
    private $date_created;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $created_by_user;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $monastery_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $patrocinium;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $gs_persons;

    /**
     * @ORM\Column(type="string", length=63, nullable=true)
     */
    private $selection_criteria;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $last_change;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $changed_by_user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $founder;

    /**
     * @ORM\OneToMany(targetEntity="Office", mappedBy="monastery")
     * @ORM\JoinColumn(name="wiagid", referencedColumnName="id_monastery")
     */
    private $office;

    /**
     * @ORM\OneToMany(targetEntity=MonasteryLocation::class, mappedBy="monastery")
     * @ORM\JoinColumn(name="wiagid", referencedColumnName="wiagid_monastery")
     */
    private $locations;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
    }


    public function getIdMonastery(): ?int
    {
        return $this->id_monastery;
    }

    public function setIdMonastery(int $id_monastery): self
    {
        $this->id_monastery = $id_monastery;

        return $this;
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

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeInterface $date_created): self
    {
        $this->date_created = $date_created;

        return $this;
    }

    public function getCreatedByUser(): ?int
    {
        return $this->created_by_user;
    }

    public function setCreatedByUser(?int $created_by_user): self
    {
        $this->created_by_user = $created_by_user;

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

    public function getMonasteryName(): ?string
    {
        return $this->monastery_name;
    }

    public function setMonasteryName(?string $monastery_name): self
    {
        $this->monastery_name = $monastery_name;

        return $this;
    }

    public function getPatrocinium(): ?string
    {
        return $this->patrocinium;
    }

    public function setPatrocinium(?string $patrocinium): self
    {
        $this->patrocinium = $patrocinium;

        return $this;
    }

    public function getGsPersons(): ?string
    {
        return $this->gs_persons;
    }

    public function setGsPersons(?string $gs_persons): self
    {
        $this->gs_persons = $gs_persons;

        return $this;
    }

    public function getSelectionCriteria(): ?string
    {
        return $this->selection_criteria;
    }

    public function setSelectionCriteria(?string $selection_criteria): self
    {
        $this->selection_criteria = $selection_criteria;

        return $this;
    }

    public function getLastChange(): ?\DateTimeInterface
    {
        return $this->last_change;
    }

    public function setLastChange(?\DateTimeInterface $last_change): self
    {
        $this->last_change = $last_change;

        return $this;
    }

    public function getChangedByUser(): ?int
    {
        return $this->changed_by_user;
    }

    public function setChangedByUser(?int $changed_by_user): self
    {
        $this->changed_by_user = $changed_by_user;

        return $this;
    }

    public function getFounder(): ?string
    {
        return $this->founder;
    }

    public function setFounder(?string $founder): self
    {
        $this->founder = $founder;

        return $this;
    }

    /**
     * @return Collection|MonasteryLocation[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(MonasteryLocation $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations[] = $location;
            $location->setMonastery($this);
        }

        return $this;
    }

    public function removeLocation(MonasteryLocation $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
            // set the owning side to null (unless already changed)
            if ($location->getMonastery() === $this) {
                $location->setMonastery(null);
            }
        }

        return $this;
    }
}
