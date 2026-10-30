<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Persistence\Dal;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class ImportJobEntity extends Entity
{
    protected string $id;

    protected string $source = '';

    protected string $status = 'queued';

    protected int $received = 0;

    protected int $accepted = 0;

    protected int $rejected = 0;

    protected ?string $errorSummary = null;

    protected ?\DateTimeInterface $startedAt = null;

    protected ?\DateTimeInterface $finishedAt = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getReceived(): int
    {
        return $this->received;
    }

    public function setReceived(int $value): void
    {
        $this->received = $value;
    }

    public function getAccepted(): int
    {
        return $this->accepted;
    }

    public function setAccepted(int $value): void
    {
        $this->accepted = $value;
    }

    public function getRejected(): int
    {
        return $this->rejected;
    }

    public function setRejected(int $value): void
    {
        $this->rejected = $value;
    }

    public function getErrorSummary(): ?string
    {
        return $this->errorSummary;
    }

    public function setErrorSummary(?string $value): void
    {
        $this->errorSummary = $value;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $value): void
    {
        $this->startedAt = $value;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $value): void
    {
        $this->finishedAt = $value;
    }
}
