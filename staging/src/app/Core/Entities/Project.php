<?php


namespace CMR\Staging\App\Core\Entities;


class Project
{
    /**
     * Project constructor.
     * @param int $id
     */
    public function __construct(
        private int $id, // For the moment it's the only data important for the application, it will change in future releases
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
}