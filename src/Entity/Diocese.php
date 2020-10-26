<?php

namespace App\Entity;

use App\Repository\DioceseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DioceseRepository::class)
 */
class Diocese
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id_diocese;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $diocese;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ecclesiastical_province;

    /**
     * @ORM\Column(type="string", length=4000, nullable=true)
     */
    private $note_diocese;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $diocese_status;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $bishopric_seat;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $date_of_founding;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $date_of_dissolution;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment_authority_file;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $gatz_pages;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $altes_reich;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $diocese_gs;

    /**
     * @ORM\Column(type="string", length=4000, nullable=true)
     */
    private $note_bishopric_seat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdDiocese(): ?int
    {
        return $this->id_diocese;
    }

    public function setIdDiocese(int $id_diocese): self
    {
        $this->id_diocese = $id_diocese;

        return $this;
    }

    public function getDiocese(): ?string
    {
        return $this->diocese;
    }

    public function setDiocese(string $diocese): self
    {
        $this->diocese = $diocese;

        return $this;
    }

    public function getEcclesiasticalProvince(): ?string
    {
        return $this->ecclesiastical_province;
    }

    public function setEcclesiasticalProvince(?string $ecclesiastical_province): self
    {
        $this->ecclesiastical_province = $ecclesiastical_province;

        return $this;
    }

    public function getNoteDiocese(): ?string
    {
        return $this->note_diocese;
    }

    public function setNoteDiocese(?string $note_diocese): self
    {
        $this->note_diocese = $note_diocese;

        return $this;
    }

    public function getDioceseStatus(): ?string
    {
        return $this->diocese_status;
    }

    public function setDioceseStatus(?string $diocese_status): self
    {
        $this->diocese_status = $diocese_status;

        return $this;
    }

    public function getBishopricSeat(): ?string
    {
        return $this->bishopric_seat;
    }

    public function setBishopricSeat(?string $bishopric_seat): self
    {
        $this->bishopric_seat = $bishopric_seat;

        return $this;
    }

    public function getDateOfFounding(): ?string
    {
        return $this->date_of_founding;
    }

    public function setDateOfFounding(?string $date_of_founding): self
    {
        $this->date_of_founding = $date_of_founding;

        return $this;
    }

    public function getDateOfDissolution(): ?string
    {
        return $this->date_of_dissolution;
    }

    public function setDateOfDissolution(?string $date_of_dissolution): self
    {
        $this->date_of_dissolution = $date_of_dissolution;

        return $this;
    }

    public function getCommentAuthorityFile(): ?string
    {
        return $this->comment_authority_file;
    }

    public function setCommentAuthorityFile(?string $comment_authority_file): self
    {
        $this->comment_authority_file = $comment_authority_file;

        return $this;
    }

    public function getGatzPages(): ?string
    {
        return $this->gatz_pages;
    }

    public function setGatzPages(?string $gatz_pages): self
    {
        $this->gatz_pages = $gatz_pages;

        return $this;
    }

    public function getAltesReich(): ?bool
    {
        return $this->altes_reich;
    }

    public function setAltesReich(?bool $altes_reich): self
    {
        $this->altes_reich = $altes_reich;

        return $this;
    }

    public function getDioceseGs(): ?bool
    {
        return $this->diocese_gs;
    }

    public function setDioceseGs(?bool $diocese_gs): self
    {
        $this->diocese_gs = $diocese_gs;

        return $this;
    }

    public function getNoteBishopricSeat(): ?string
    {
        return $this->note_bishopric_seat;
    }

    public function setNoteBishopricSeat(?string $note_bishopric_seat): self
    {
        $this->note_bishopric_seat = $note_bishopric_seat;

        return $this;
    }
}
