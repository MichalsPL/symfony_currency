<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExchangeRateHistoryRepository")
 */
class ExchangeRateHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency", inversedBy="exchangeRateHistories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    /**

    /**
     * @ORM\Column(type="float", nullable=false)
     */
    private $mid_price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }


    public function getMidPrice(): ?float
    {
        return $this->mid_price;
    }

    public function setMidPrice(?float $mid_price): self
    {
        $this->mid_price = $mid_price;

        return $this;
    }
}
