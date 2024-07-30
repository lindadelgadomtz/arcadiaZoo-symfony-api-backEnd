<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['animal:read', 'animal:write', 'habitat:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['animal:read', 'animal:write'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 50)]
    #[Groups(['animal:read', 'animal:write'])]
    private ?string $etat = null;

    #[ORM\OneToMany(mappedBy: 'animal', targetEntity: RapportVeterinaire::class, cascade: ["persist"])]
    #[Groups(['animal:read', 'animal:write'])]
    private Collection $rapportVeterinaires;

    #[ORM\ManyToOne(inversedBy: 'animals', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['animal:read', 'animal:write', 'habitat:read'])]
    private ?Race $race = null;

    #[ORM\ManyToOne(inversedBy: 'animals', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['animal:read', 'animal:write'])]
    private ?Habitat $habitat = null;

    #[ORM\ManyToMany(targetEntity: Image::class, mappedBy: 'animals', cascade: ["persist"])]
    #[Groups(['animal:read', 'animal:write'])]
    private Collection $images;

    #[ORM\ManyToMany(targetEntity: Gallery::class, mappedBy: 'animals')]
    #[Groups(['animal:read', 'animal:write', 'gallery:read'])]
    private Collection $galleries;

    public function __construct()
    {
        $this->rapportVeterinaires = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->galleries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    /**
     * @return Collection<int, RapportVeterinaire>
     */
    public function getRapportVeterinaires(): Collection
    {
        return $this->rapportVeterinaires;
    }

    public function addRapportVeterinaire(RapportVeterinaire $rapportVeterinaire): static
    {
        if (!$this->rapportVeterinaires->contains($rapportVeterinaire)) {
            $this->rapportVeterinaires->add($rapportVeterinaire);
            $rapportVeterinaire->setAnimal($this);
        }
        return $this;
    }

    public function removeRapportVeterinaire(RapportVeterinaire $rapportVeterinaire): static
    {
        if ($this->rapportVeterinaires->removeElement($rapportVeterinaire)) {
            if ($rapportVeterinaire->getAnimal() === $this) {
                $rapportVeterinaire->setAnimal(null);
            }
        }
        return $this;
    }

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): static
    {
        $this->race = $race;
        return $this;
    }

    public function getHabitat(): ?Habitat
    {
        return $this->habitat;
    }

    public function setHabitat(?Habitat $habitat): static
    {
        $this->habitat = $habitat;
        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->addAnimal($this);
        }
        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            $image->removeAnimal($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, Gallery>
     */
    public function getGalleries(): Collection
    {
        return $this->galleries;
    }

    public function addGallery(Gallery $gallery): static
    {
        if (!$this->galleries->contains($gallery)) {
            $this->galleries->add($gallery);
            $gallery->addAnimal($this); // Ensure Gallery has addAnimal method
        }
        return $this;
    }

    public function removeGallery(Gallery $gallery): static
    {
        if ($this->galleries->removeElement($gallery)) {
            $gallery->removeAnimal($this); // Ensure Gallery has removeAnimal method
        }
        return $this;
    }
}
