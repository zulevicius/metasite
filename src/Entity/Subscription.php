<?php

namespace App\Entity;

/**
 * Entity to manipulate person's data.
 *
 * Class Subscription
 */
class Subscription implements EntityInterface
{
    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $fullName;

    /**
     * @var string
     */
    private $categories;

    /**
     * @var string
     */
    private $registrationDate;


    /**
     * @return string|null
     */
    public function primaryKeyVal(): ?string
    {
        return $this->getEmail();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Subscription
     */
    public function setId(int $id): self
    {
        if (!is_int($id)) {
            throw new ValidationException('ID must be numeric');
        }
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Subscription
     */
    public function setEmail(string $email): self
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Valid email must be provided');
        }
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     *
     * @return Subscription
     */
    public function setFullName(string $fullName): self
    {
        if (empty($fullName)) {
            throw new ValidationException('Full name must be provided');
        }

        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategories(): ?string
    {
        return $this->categories;
    }

    /**
     * @param string $categories
     *
     * @return Subscription
     */
    public function setCategories(string $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegistrationDate(): ?string
    {
        return $this->registrationDate;
    }

    /**
     * @param string $registrationDate
     *
     * @return Subscription
     */
    public function setRegistrationDate(string $registrationDate): self
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }
}
