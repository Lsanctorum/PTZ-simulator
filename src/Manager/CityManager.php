<?php

namespace PtzSimulator\Manager;

use PtzSimulator\Entity\City;
use PtzSimulator\Repository\CityRepository;

/**
 * Class CityManager
 *
 * @package PtzSimulator\Entity
 */
class CityManager
{
    /**
     * @var CityRepository
     */
    private $repository;

    /**
     * CityManager constructor.
     */
    public function __construct()
    {
        $this->repository = new CityRepository(new EntityManager());
    }

    /**
     * @param City $city
     *
     * @return City
     */
    public function persist(City $city): City
    {
        $this->repository->create($city);
        return $city;
    }

    /**
     * @param string $id
     * @return City|null
     */
    public function fetchOne(string $id): ?City
    {
        $data = $this->repository->fetchOne($id);

        if (null !== $data) {
            return $this->buildCity($data);
        }

        return null;
    }

    /**
     * @param string ...$ids
     *
     * @return array
     */
    public function findMultipleByIds(string ...$ids): array
    {
        $data = $this->repository->findMultipleByIds(...$ids);
        return $this->hydrateCities($data);
    }

    /**
     * @param string $name
     * @return string
     */
    public function formatDataForSelect(string $name): string
    {
        $cities = $this->getByName($name);
        $options = [];
        foreach ($cities as $city) {
            $options[] = [
                'value' => $city->getId(),
                'label' => sprintf('%s (%s)', $city->getName(), implode(', ', $city->getZipCodes()))
            ];
        }

        return json_encode($options);
    }

    /**
     * @param array $data
     * @return City
     */
    public function buildCity(array $data): City
    {
        $zipCodes = is_string($data['zip_codes']) ? json_decode($data['zip_codes'], true) : $data['zip_codes'];
        return (new City())
            ->setId($data['id'])
            ->setZone($data['zone'])
            ->setName($data['name'])
            ->setDepartment($data['department'])
            ->setZipCodes($zipCodes)
            ->setLatitude($data['latitude'] ?? null)
            ->setLongitude($data['longitude'] ?? null);
    }

    /**
     * @param City $city
     * @param int $rayon
     *
     * @return array|City[]
     */
    public function findByRayon(City $city, int $rayon)
    {
        return $this->hydrateCities($this->repository->findByRayon($city, $rayon));
    }

    /**
     * @param string $name
     *
     * @return array|City[]
     */
    private function getByName(string $name): array
    {
        return $this->hydrateCities($this->repository->findByName($name));
    }

    /**
     * @param array $data
     *
     * @return array|City[]
     */
    private function hydrateCities(array $data): array
    {
        foreach ($data as $datum) {
            $cities[] = $this->buildCity($datum);
        }

        return $cities ?? [];
    }
}