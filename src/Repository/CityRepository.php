<?php

namespace PtzSimulator\Repository;

use PtzSimulator\Entity\City;
use PtzSimulator\Manager\EntityManager;

/**
 * Class CityRepository
 *
 * @package PtzSimulator\Repository
 */
class CityRepository
{
    private const TABLE = 'cities';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * CityRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param City $city
     */
    public function create(City $city)
    {
        $query = $this->entityManager
            ->getConnection()
            ->prepare(
                sprintf(
                    'INSERT INTO %s (id, name, zone, department, zip_codes, latitude, longitude, cos_lat, sin_lat, cos_lng, sin_lng) VALUES 
                    (:id, :name, :zone, :department , :zip_codes, :latitude, :longitude, :cos_lat, :sin_lat, :cos_lng, :sin_lng)',
                    self::TABLE
                )
            );

        $query->execute([
            ':id' => $city->getId(),
            ':name' => $city->getName(),
            ':zone' => $city->getZone(),
            ':department' => $city->getDepartment(),
            ':zip_codes' => json_encode($city->getZipCodes()),
            ':latitude' => $city->getLatitude(),
            ':longitude' => $city->getLongitude(),
            ':cos_lat' => $city->getCosLat(),
            ':sin_lat' => $city->getSinLat(),
            ':cos_lng' => $city->getCosLng(),
            ':sin_lng' => $city->getSinLng()
        ]);
    }

    /**
     * @param City $city
     * @param int $rayon
     *
     * @return array
     */
    public function findByRayon(City $city, int $rayon): array
    {
        $connection = $this->entityManager->getConnection();

        $query = $connection->query(sprintf(
            'SELECT %s FROM %s
              WHERE %s * sin_lat + %s * cos_lat * (cos_lng * %s + sin_lng * %s) > %s ORDER BY name ASC',
            $this->getDefaultFields(['latitude', 'longitude']),
            self::TABLE,
            $city->getSinLat(),
            $city->getCosLat(),
            $city->getCosLng(),
            $city->getSinLng(),
            cos($rayon / 6371)
        ));

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function findByName(string $name): array
    {
        $query = $this->entityManager
            ->getConnection()
            ->prepare(
                sprintf(
                    'SELECT %s FROM %s WHERE name LIKE :name',
                    $this->getDefaultFields(),
                    self::TABLE
                )
            );

        $query->bindValue(':name', '%' . $name . '%');
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $id
     *
     * @return null|array
     */
    public function fetchOne(string $id): ?array
    {
        $query = $this->entityManager
            ->getConnection()
            ->prepare(
                sprintf(
                    'SELECT %s FROM %s WHERE id = ?',
                    $this->getDefaultFields(['latitude', 'longitude']),
                    self::TABLE
                )
            );
        $query->bindValue(1, $id);
        $query->execute();

        $result = $query->fetch(\PDO::FETCH_ASSOC);

        return $result === false ? [] : $result;
    }

    /**
     * @param string ...$ids
     *
     * @return array
     */
    public function findMultipleByIds(string ...$ids): array
    {
        $query = $this->entityManager
            ->getConnection()
            ->prepare(
                sprintf(
                    'SELECT %s FROM %s WHERE id IN (%s) ORDER BY name ASC',
                    $this->getDefaultFields(['latitude', 'longitude']),
                    self::TABLE,
                    implode(', ', array_fill(0, count($ids), '?'))
                )
            );

        $i = 1;
        foreach ($ids as $id) {
            $query->bindValue($i, $id);
            $i++;
        }

        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $result === false ? [] : $result;
    }

    /**
     * @param array $otherFields
     *
     * @return string
     */
    private function getDefaultFields(array $otherFields = []): string
    {
        $fields = ['id', 'name', 'zone', 'zip_codes', 'department'];
        $fields = array_merge($fields, $otherFields);

        return implode(', ', $fields);
    }
}