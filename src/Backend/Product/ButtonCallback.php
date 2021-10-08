<?php

namespace MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product;

use Contao\StringUtil;

/**
 * Class ButtonCallback.
 *
 * @package MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product
 */
class ButtonCallback {

    /**
     * Add variant price generation button.
     *
     * @param array $buttons
     *   The buttons.
     *
     * @return array
     *   The updated buttons array.
     */
    public function addVariantPriceGenerationButton(array $buttons): array {
        $buttons[VariantPriceGenerator::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_BUTTON_ID] = '<button 
            type="submit"
            name="' . VariantPriceGenerator::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_BUTTON_ID . '"
            id="' . VariantPriceGenerator::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_BUTTON_ID . '"
            class="tl_submit">' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product']['generate_variant_price_by_attribute_option'][0]) . '</button> ';
        return $buttons;
    }

}
