<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product\VariantPriceGenerator;

// We have to add a custom price field as the default price field gets removed by Isotope,
// when the attribute is used for product variants.
// See: isotope/isotope-core/system/modules/isotope/library/Isotope/Backend/AttributeOption/Callback.php:67

$GLOBALS['TL_DCA']['tl_iso_attribute_option']['fields'][VariantPriceGenerator::TL_ISO_ATTRIBUTE_OPTION_CUSTOM_PRICE_FIELD_NAME] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_iso_attribute_option'][VariantPriceGenerator::TL_ISO_ATTRIBUTE_OPTION_CUSTOM_PRICE_FIELD_NAME],
    'inputType' => 'text',
    'eval'      => ['mandatory' => TRUE, 'maxlength' => 13, 'rgxp' => 'digit', 'tl_class' => 'w50 clr'],
    'sql'       => "varchar(13) NOT NULL default ''",
];

PaletteManipulator::create()
    ->addField(VariantPriceGenerator::TL_ISO_ATTRIBUTE_OPTION_CUSTOM_PRICE_FIELD_NAME, 'label')
    ->applyToPalette('option', 'tl_iso_attribute_option');
