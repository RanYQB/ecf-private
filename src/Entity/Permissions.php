<?php

namespace App\Entity;

use App\Repository\PermissionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionsRepository::class)]
class Permissions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $newsletter = null;

    #[ORM\Column]
    private ?bool $planning_management = null;

    #[ORM\Column]
    private ?bool $drink_sales = null;

    #[ORM\Column]
    private ?bool $video_courses = null;

    #[ORM\Column]
    private ?bool $prospect_reminders = null;

    #[ORM\Column]
    private ?bool $sponsorship = null;

    #[ORM\Column]
    private ?bool $free_wifi = null;

    #[ORM\Column]
    private ?bool $flexible_hours = null;

    #[ORM\OneToOne(inversedBy: 'permissions', cascade: ['persist', 'remove'])]
    private ?Partner $partner = null;

    #[ORM\OneToOne(inversedBy: 'permissions', cascade: ['persist', 'remove'])]
    private ?Structure $structure = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isNewsletter(): ?bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): self
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function isPlanningManagement(): ?bool
    {
        return $this->planning_management;
    }

    public function setPlanningManagement(bool $planning_management): self
    {
        $this->planning_management = $planning_management;

        return $this;
    }

    public function isDrinkSales(): ?bool
    {
        return $this->drink_sales;
    }

    public function setDrinkSales(bool $drink_sales): self
    {
        $this->drink_sales = $drink_sales;

        return $this;
    }

    public function isVideoCourses(): ?bool
    {
        return $this->video_courses;
    }

    public function setVideoCourses(bool $video_courses): self
    {
        $this->video_courses = $video_courses;

        return $this;
    }

    public function isProspectReminders(): ?bool
    {
        return $this->prospect_reminders;
    }

    public function setProspectReminders(bool $prospect_reminders): self
    {
        $this->prospect_reminders = $prospect_reminders;

        return $this;
    }

    public function isSponsorship(): ?bool
    {
        return $this->sponsorship;
    }

    public function setSponsorship(bool $sponsorship): self
    {
        $this->sponsorship = $sponsorship;

        return $this;
    }

    public function isFreeWifi(): ?bool
    {
        return $this->free_wifi;
    }

    public function setFreeWifi(bool $free_wifi): self
    {
        $this->free_wifi = $free_wifi;

        return $this;
    }

    public function isFlexibleHours(): ?bool
    {
        return $this->flexible_hours;
    }

    public function setFlexibleHours(bool $flexible_hours): self
    {
        $this->flexible_hours = $flexible_hours;

        return $this;
    }

    public function getPartner(): ?Partner
    {
        return $this->partner;
    }

    public function setPartner(?Partner $partner): self
    {
        $this->partner = $partner;

        return $this;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }
}
