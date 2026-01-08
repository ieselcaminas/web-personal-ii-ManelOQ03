<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $file = null;

    #[ORM\Column]
    private ?int $numLikes = null;

    #[ORM\Column]
    private ?int $numViews = null;

    #[ORM\Column]
    private ?int $numDonwLoads = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getNumLikes(): ?int
    {
        return $this->numLikes;
    }

    public function setNumLikes(int $numLikes): static
    {
        $this->numLikes = $numLikes;

        return $this;
    }

    public function getNumViews(): ?int
    {
        return $this->numViews;
    }

    public function setNumViews(int $numViews): static
    {
        $this->numViews = $numViews;

        return $this;
    }

    public function getNumDonwLoads(): ?int
    {
        return $this->numDonwLoads;
    }

    public function setNumDonwLoads(int $numDonwLoads): static
    {
        $this->numDonwLoads = $numDonwLoads;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
