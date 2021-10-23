<?php

namespace CMR\Staging\App\Core\Entities;

class Subject
{
    public function __construct(
        private int $id,
        private string $first_name,
        private string $last_name,
        private string $title,
        private string $licenseNumber,
    )
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getLicenseNumber(): string
    {
        return $this->licenseNumber;
    }
}