<?php

namespace MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\ContaoIsotopeVariantPriceByAttributeOptionBundle;

/**
 * Class Plugin.
 *
 * @package MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\ContaoManager
 */
class Plugin implements BundlePluginInterface {

    /**
     * {@inheritdoc}.
     */
    public function getBundles(ParserInterface $parser) {
        return [
            BundleConfig::create(ContaoIsotopeVariantPriceByAttributeOptionBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    'isotope',
                ]),
        ];
    }

}
