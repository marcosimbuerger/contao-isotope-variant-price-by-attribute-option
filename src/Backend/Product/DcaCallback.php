<?php

namespace MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product;

use Contao\Backend;
use Contao\DC_Table;
use Contao\Environment;
use Contao\Input;

/**
 * Class DcaCallback.
 *
 * @package MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product
 */
class DcaCallback extends Backend {

    /**
     * Called by onload_callback.
     *
     * @param \Contao\DC_Table $dataContainer
     *   The Contao data container (DC).
     */
    public function onLoad(DC_Table $dataContainer): void {
        if (Input::post('FORM_SUBMIT') === 'tl_select') {
            if (isset($_POST[VariantPriceGenerator::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_BUTTON_ID])) {
                $this->redirect(str_replace('act=select', 'act=' . VariantPriceGenerator::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_ACTION_NAME, Environment::get('request')));
            }
        }

        if (Input::get('act') === VariantPriceGenerator::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_ACTION_NAME) {
            $dataContainer->{VariantPriceGenerator::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_ACTION_NAME} = function() {
                /** @var \MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product\VariantPriceGenerator $variantPriceGenerator */
                $variantPriceGenerator = new VariantPriceGenerator();
                return $variantPriceGenerator->generate();
            };
        }
    }

}
