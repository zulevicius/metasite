<?php

namespace App\Entity;

class Category implements EntityInterface
{
    /**
     * @var string
     */
    private $title;


    /**
     * @return string|null
     */
    public function primaryKeyVal(): ?string
    {
        return $this->getTitle();
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }


    /**
     * @param string $email
     *
     * @return Subscription
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
