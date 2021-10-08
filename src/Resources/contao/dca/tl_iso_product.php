<?php

use MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product\ButtonCallback;
use MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product\DcaCallback;

// Calls the custom data container 'DC_ProductDataExtension'.
// It has to be custom as tl_iso_product already calls an own custom data container 'DC_ProductData'.
$GLOBALS['TL_DCA']['tl_iso_product']['config']['dataContainer'] = 'ProductDataExtension';

// Callbacks.
$GLOBALS['TL_DCA']['tl_iso_product']['config']['onload_callback'][] = [DcaCallback::class, 'onLoad'];
$GLOBALS['TL_DCA']['tl_iso_product']['select']['buttons_callback'][] = [ButtonCallback::class, 'addVariantPriceGenerationButton'];
