<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(fields: ['status'])]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 200)]
    public string $title {
        set => trim($value);
    }

    #[ORM\Column(type: Types::TEXT)]
    public string $body;

    #[ORM\Column(length: 20)]
    public private(set) string $status = 'draft';

    // The author lives in the Auth service, so this is a plain id, not a relation
    #[ORM\Column]
    public private(set) int $authorId;

    #[ORM\ManyToOne]
    public ?Category $category = null;

    #[ORM\Column]
    public private(set) \DateTimeImmutable $createdAt;

    #[ORM\Column]
    public private(set) \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    public private(set) ?\DateTimeImmutable $publishedAt = null;

    public function __construct(string $title, string $body, int $authorId)
    {
        $this->title = $title;
        $this->body = $body;
        $this->authorId = $authorId;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
