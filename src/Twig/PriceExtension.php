<?php


namespace PtzSimulator\Twig;

use NumberFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class PriceExtension
 *
 * @package PtzSimulator\Twig
 */
class PriceExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
        ];
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function formatPrice($number): string
    {
        $numberFormatter = new NumberFormatter( 'fr_FR', NumberFormatter::CURRENCY );

        $amount = $numberFormatter->formatCurrency($number, 'EUR');
        return preg_replace('/[\s]+/mu', ' ', $amount);
    }
}