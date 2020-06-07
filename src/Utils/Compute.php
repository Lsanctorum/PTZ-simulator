<?php


namespace PtzSimulator\Utils;

/**
 * Class Compute
 *
 * @package PtzSimulator\Utils
 */
class Compute
{
    /**
     * Get the median coordinate point to center the map
     *
     * @param array $coordinates
     *
     * @return float
     */
    public static function getMiddleCoordinate(array $coordinates): float
    {
        $closest = null;
        $search = (array_sum($coordinates) / count($coordinates));
        foreach ($coordinates as $item) {
            if (null === $closest || abs($search - $closest) > abs($item - $search)) {
                $closest = $item;
            }
        }

        return $closest;
    }
}