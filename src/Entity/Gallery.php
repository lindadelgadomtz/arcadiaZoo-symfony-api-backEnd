<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GalleryRepository::class)]
class Gallery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?string $urlImage = null;

    #[ORM\ManyToOne(targetEntity: Habitat::class, inversedBy: 'Gallery')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?Habitat $habitat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getUrlImage(): ?string
    {
        return $this->urlImage;
    }

    public function setUrlImage(string $urlImage): self
    {
        $this->urlImage = $urlImage;
        return $this;
    }

    public function getHabitat(): ?Habitat
    {
        return $this->habitat;
    }

    public function setHabitat(?Habitat $habitat): self
    {
        $this->habitat = $habitat;
        return $this;
    }
}
