<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait IsActiveTrait
{
    #[ORM\Column]
    private ?bool $is_active = null;

    public function isIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }
}
