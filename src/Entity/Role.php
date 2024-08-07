<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $label = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'roles')]
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    //*#[ORM\OneToMany(mappedBy: 'role', targetEntity: Utilisateur::class, cascade: ['remove'])]
    //*private Collection $utilisateurs;

    //public function __construct()
    //*{
    //*    $this->utilisateurs = new ArrayCollection();
    //*}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addRole($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeRole($this);
        }

        return $this;
    }
}

    /**
     * @return Collection<int, Utilisateur>
     
    *public function getUtilisateurs(): Collection
    *{
      *  return $this->utilisateurs;
    *}

    *public function addUtilisateur(Utilisateur $utilisateur): static
    *{
    *    if (!$this->utilisateurs->contains($utilisateur)) {
    *        $this->utilisateurs->add($utilisateur);
    *        $utilisateur->setRole($this);
    *    }
*
    *    return $this;
    *}

   * public function removeUtilisateur(Utilisateur $utilisateur): static
   * {
    *    if ($this->utilisateurs->removeElement($utilisateur)) {
    *        // set the owning side to null (unless already changed)
    *        if ($utilisateur->getRole() === $this) {
   *             $utilisateur->setRole(null);
    *        }
    *    }

    *    return $this;
    *}*/

