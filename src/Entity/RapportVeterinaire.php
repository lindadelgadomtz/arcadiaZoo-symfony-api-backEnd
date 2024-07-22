<?php

namespace App\Entity;

use App\Repository\RapportVeterinaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RapportVeterinaireRepository::class)]
class RapportVeterinaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 50)]
    private ?string $detail = null;

    /*#[ORM\ManyToOne(inversedBy: 'rapportVeterinaires', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $username = null; */

    #[ORM\ManyToOne(inversedBy: 'rapportVeterinaires', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Animal $animal = null;

    #[ORM\Column(length: 50)]
    private ?string $etat_animal = null;

    #[ORM\Column(length: 50)]
    private ?string $nourriture = null;

    #[ORM\Column]
    private ?int $nourriture_grammage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): static
    {
        $this->detail = $detail;

        return $this;
    }

    /*public function getUsername(): ?Utilisateur
    {
        return $this->username;
    }

    public function setUsername(?Utilisateur $username): static
    {
        $this->username = $username;

        return $this;
    }*/
    

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): static
    {
        $this->animal = $animal;

        return $this;
    }

    public function getEtatAnimal(): ?string
    {
        return $this->etat_animal;
    }

    public function setEtatAnimal(string $etat_animal): static
    {
        $this->etat_animal = $etat_animal;

        return $this;
    }

    public function getNourriture(): ?string
    {
        return $this->nourriture;
    }

    public function setNourriture(string $nourriture): static
    {
        $this->nourriture = $nourriture;

        return $this;
    }

    public function getNourritureGrammage(): ?int
    {
        return $this->nourriture_grammage;
    }

    public function setNourritureGrammage(int $nourriture_grammage): static
    {
        $this->nourriture_grammage = $nourriture_grammage;

        return $this;
    }
}
