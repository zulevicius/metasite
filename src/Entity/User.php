<?php

namespace App\Entity;

class User implements EntityInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var string
     */
    private $password;


    /**
     * @return string|null
     */
    public function primaryKeyVal(): ?string
    {
        return $this->getUsername();
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return Subscription
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getRoles(): ?array
    {
        if (empty($this->roles)) {
            return ['ROLE_USER'];
        }
        $roles = explode('|', $this->roles);
        return $roles;
    }

    /**
     * @param string $roles
     *
     * @return User
     */
    public function setRoles(string $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
