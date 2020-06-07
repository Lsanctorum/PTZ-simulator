<?php

namespace PtzSimulator\Entity;

/**
 * Class City
 * @package PtzSimulator\Entity
 */
class City
{
    /**
     * code INSEE
     *
     * @var string
     */
    private $id;

    /**
     * city name
     *
     * @var string
     */
    private $name;

    /** @var string */
    private $zone;

    /** @var array */
    private $zipCodes;

    /** @var string */
    private $department;

    /** @var null|float */
    private $latitude;

    /** @var null|float */
    private $longitude;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return City
     */
    public function setId(string $id): City
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return City
     */
    public function setName(string $name): City
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getZone(): string
    {
        return $this->zone;
    }

    /**
     * @param string $zone
     *
     * @return City
     */
    public function setZone(string $zone): City
    {
        $this->zone = $zone;
        return $this;
    }

    /**
     * @return array
     */
    public function getZipCodes(): array
    {
        return $this->zipCodes;
    }

    /**
     * @param array $zipCodes
     *
     * @return City
     */
    public function setZipCodes(array $zipCodes): City
    {
        $this->zipCodes = $zipCodes;
        return $this;
    }

    /**
     * @return string
     */
    public function getDepartment(): string
    {
        return $this->department;
    }

    /**
     * @param string $department
     *
     * @return City
     */
    public function setDepartment(string $department): City
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param float|null $latitude
     *
     * @return City
     */
    public function setLatitude(?float $latitude): City
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param float|null $longitude
     *
     * @return City
     */
    public function setLongitude(?float $longitude): City
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return float
     */
    public function getCosLat(): float
    {
        return cos($this->latitude * pi() / 180);
    }

    /**
     * @return float
     */
    public function getSinLat(): float
    {
        return sin($this->latitude * pi() / 180);
    }

    /**
     * @return float
     */
    public function getCosLng(): float
    {
        return cos($this->longitude * pi() / 180);
    }

    /**
     * @return float
     */
    public function getSinLng(): float
    {
        return sin($this->longitude * pi() / 180);
    }
}