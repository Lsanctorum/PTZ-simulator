<?php


namespace PtzSimulator\Entity;

/**
 * Class Ptz
 * @package PtzSimulator\Entity
 */
class Ptz
{
    private const MAX_SUM_PTZ = [
        'A' => [
            1 => 150000,
            2 => 210000,
            3 => 255000,
            4 => 300000,
            5 => 345000
        ],
        'A bis' => [
            1 => 150000,
            2 => 210000,
            3 => 255000,
            4 => 300000,
            5 => 345000
        ],
        'B1' => [
            1 => 135000,
            2 => 189000,
            3 => 230000,
            4 => 270000,
            5 => 311000
        ],
        'B2' => [
            1 => 110000,
            2 => 154000,
            3 => 187000,
            4 => 220000,
            5 => 253000
        ],
        'C' => [
            1 => 100000,
            2 => 140000,
            3 => 170000,
            4 => 200000,
            5 => 230000
        ],
    ];

    private const MAX_FISCAL_REVENUE = [
        'A' => 37000,
        'A bis' => 37000,
        'B1' => 30000,
        'B2' => 27000,
        'C' => 24000
    ];

    /**
     * PTZ coef according to the people number
     */
    private const PTZ_COEF = [
        1 => 1,
        2 => 1.4,
        3 => 1.7,
        4 => 2,
        5 => 2.3,
        6 => 2.6,
        7 => 2.9,
        8 => 3.2
    ];

    /**
     * For the A zone, the discount max is 40% of the price
     * For a good to 150000 = 150000 * 0.4
     */
    private const MAX_DISCOUNT_BY_ZONE = [
        'A' => 0.4,
        'A bis' => 0.4,
        'B1' => 0.4,
        'B2' => 0.2,
        'C' => 0.2
    ];

    /**
     * fiscal revenue of user year n-2
     *
     * @var int
     */
    private $fiscalRevenue;

    /**
     * Number of people which will live in good
     *
     * @var int
     */
    private $people;

    /**
     * @var null|City
     */
    private $city;

    /**
     * The good price min
     *
     * @var int
     */
    private $price;

    /**
     * @var array
     */
    private $results;

    /**
     * Ptz constructor.
     * @param int $people
     */
    public function __construct(int $people = 1)
    {
        $this->people = $people;
        $this->results = [];
    }

    /**
     * @return int
     */
    public function getFiscalRevenue(): int
    {
        return $this->fiscalRevenue;
    }

    /**
     * @param int $fiscalRevenue
     *
     * @return Ptz
     */
    public function setFiscalRevenue(int $fiscalRevenue): Ptz
    {
        $this->fiscalRevenue = $fiscalRevenue;
        return $this;
    }

    /**
     * @return int
     */
    public function getPeople(): int
    {
        return $this->people;
    }

    /**
     * @param int $people
     *
     * @return Ptz
     */
    public function setPeople(int $people): Ptz
    {
        $this->people = $people;
        return $this;
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return $this->city;
    }

    /**
     * @param City $city
     *
     * @return Ptz
     */
    public function setCity(City $city): Ptz
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return Ptz
     */
    public function setPrice(int $price): Ptz
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @param array $results
     *
     * @return Ptz
     */
    public function setResults(array $results)
    {
        $this->results = $results;
        return $this;
    }

    /**
     * @return float
     */
    public function computePtz(): float
    {
        if (true === $this->isEligiblePtz()) {
            $zone = $this->city->getZone();
            $nbPeople = $this->people;
            $maxPtzValue = self::MAX_SUM_PTZ[$zone][$nbPeople >= 5 ? 5 : $nbPeople];
            $maxAmount = $this->price > $maxPtzValue ? $maxPtzValue : $this->price;

            $amountPtz = $maxAmount * self::MAX_DISCOUNT_BY_ZONE[$zone];
        }

        $this->addResult('min', $this->price, $amountPtz ?? 0);

        return $amountPtz ?? 0;
    }

    /**
     * @param int $interval
     */
    public function computePtzWithInterval(int $interval = 1000): void
    {
        $this
            ->addResult('min', $this->price, 0)
            ->addResult('max', $this->price, 0);
        if ($this->isEligiblePtz()) {
            $currentPrice = $this->price;
            $minAmountPtz = $this->computePtz();
            $this->addResult('min', $currentPrice, $minAmountPtz);

            $ptz = clone $this;
            while ($ptz->isEligiblePtz()) {
                $this->addResult('max', $ptz->price, $ptz->computePtz());
                $ptz->setPrice($ptz->getPrice() + $interval);
            }
        }
    }

    /**
     * @param string $key
     * @param int $price
     * @param int $amountPtz
     *
     * @return Ptz
     */
    private function addResult(string $key, int $price, int $amountPtz = 0): Ptz
    {
        $this->results[$key] = ['price' => $price, 'amountPtz' => $amountPtz];

        return $this;
    }

    /**
     * @return bool
     */
    private function isEligiblePtz(): bool
    {
        if (null === $this->city) {
            return false;
        }

        $maxAmount = max([$this->fiscalRevenue, $this->price / 9]);
        $familyRevenue = $maxAmount / self::PTZ_COEF[$this->people >= 8 ? 8 : $this->people];

        return $familyRevenue <= self::MAX_FISCAL_REVENUE[$this->city->getZone()];
    }
}