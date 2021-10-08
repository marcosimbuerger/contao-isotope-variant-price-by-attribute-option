<?php

namespace MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product;

use Contao\Controller;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use Isotope\Model\AttributeOption;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Psr\Log\LogLevel;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class VariantPriceGenerator.
 *
 * @package MarcoSimbuerger\IsotopeVariantPriceByAttributeOptionBundle\Backend\Product
 */
class VariantPriceGenerator {

    /**
     * The 'generate variant price by attribute option' form id.
     *
     * @var string
     */
    protected const GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_FORM_ID = 'isotope_generate_variant_price_by_attribute_option';

    /**
     * The 'generate variant price by attribute option' button id.
     *
     * @var string
     */
    public const GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_BUTTON_ID = 'generate_variant_price_by_attribute_option';

    /**
     * The 'generate variant price by attribute option' action name (GET act=).
     *
     * @var string
     */
    public const GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_ACTION_NAME = 'generate_variant_price';

    /**
     * The custom price field name in DCA tl_iso_attribute_option.
     *
     * @var string
     */
    public const TL_ISO_ATTRIBUTE_OPTION_CUSTOM_PRICE_FIELD_NAME = 'price_for_variants';

    /**
     * The database instance.
     *
     * @var \Contao\Database|null
     */
    protected $database;

    /**
     * The monolog logger.
     *
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected Logger $logger;

    /**
     * The url of the current request.
     *
     * @var string
     */
    protected string $currentRequestUrl;

    /**
     * VariantPriceGenerator constructor.
     */
    public function __construct() {
        $this->database = NULL;
        $this->logger = System::getContainer()->get('monolog.logger.contao');
        $this->currentRequestUrl = Environment::get('request');
    }

    /**
     * Generate.
     *
     * @return string|void
     *   The overview page HTML markup.
     *   Void for the second call (redirects to the product overview page).
     */
    public function generate() {
        $redirectUrl = $this->getRedirectUrl();

        // If GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_FROM_ID is present, the overview page has been submitted.
        if (Input::post('FORM_SUBMIT') === self::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_FORM_ID) {
            $this->generateVariantPrice();
            Controller::redirect($redirectUrl);
        }
        else {
            // Return overview page HTML markup if GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_FROM_ID is not present.
            // TODO: Use better solution as this Contao default.
            $messages = Message::generate();
            Message::reset();
            return '
<div id="tl_buttons">
    <a href="' . ampersand($redirectUrl) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '</a>
</div>
<h2 class="sub_headline">' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product']['generate_variant_price_by_attribute_option'][0]) . '</h2>' . $messages . '
<form action="' . ampersand($this->currentRequestUrl, TRUE) . '" id="' . self::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_FORM_ID . '" class="tl_form" method="post">
    <div class="tl_formbody_edit">
        <input type="hidden" name="FORM_SUBMIT" value="' . self::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_FORM_ID . '">
        <input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
        <div class="tl_tbox block">
          <div class="clr widget">
            ' . $this->getSelectedProductHtmlPreview() . '
          </div>
        </div>
    </div>
    <div class="tl_formbody_submit">
        <div class="tl_submit_container">
            <p class="tl_red">' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product']['generate_variant_price_by_attribute_option_generate'][1]) .  '</p>
            <input type="submit" name="generate" id="generate" class="tl_submit" accesskey="s" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product']['generate_variant_price_by_attribute_option_generate'][0]) . '">
        </div>
    </div>
</form>';
        }
    }

    /**
     * Get the redirect url.
     *
     * @return string
     */
    protected function getRedirectUrl(): string {
        return str_replace('&act=' . self::GENERATE_VARIANT_PRICE_BY_ATTRIBUTE_OPTION_ACTION_NAME, '', $this->currentRequestUrl);
    }

    /**
     * Get the database instance.
     *
     * @return \Contao\Database
     *   The Database instance.
     */
    protected function getDatabase(): Database {
        if ($this->database === NULL) {
            $this->database = \Database::getInstance();
        }

        return $this->database;
    }

    /**
     * Get the sessions ids (the selected products).
     *
     * @return array
     *   The sessions ids.
     */
    protected function getSessionIds(): array {
        /** @var \Contao\Session $sessionObject */
        $sessionObject = System::getContainer()->get('session');
        $session = $sessionObject->all();
        return $session['CURRENT']['IDS'];
    }

    /**
     * Get the selected products as HTML preview.
     *
     * @return string
     *   The HTML markup.
     */
    protected function getSelectedProductHtmlPreview(): string {
        $preview = '<ul>';
        foreach ($this->getVariantProducts() as $product) {
            $preview .= '<li> > ' . $product->name . '</li>';
        }
        $preview .= '</ul>';

        return $preview;
    }

    /**
     * Generate the variant price for the selected products.
     */
    protected function generateVariantPrice(): void {
        $variantPriceGenerated = FALSE;

        foreach ($this->getVariantProducts() as $product) {
            foreach ($product->getVariantIds() as $variantId) {
                /** @var \Isotope\Model\Product|null $variantProduct */
                if ($variantProduct = Product::findByPk($variantId)) {
                    $variantPrice = $this->getPriceForAllAttributeOptions($variantProduct);
                    if ($variantPrice > 0) {
                        if ($this->setPriceForVariantProduct($variantProduct, $variantPrice) === FALSE) {
                            $this->logError('Could not set price for variant product with id ' . $variantId, __FUNCTION__);
                        }
                        $variantPriceGenerated = TRUE;
                    }
                }
            }
        }

        if ($variantPriceGenerated === TRUE) {
            Message::addConfirmation(StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product']['generate_variant_price_by_attribute_option_successful'][0]));
        }
        else {
            Message::addError(StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product']['generate_variant_price_by_attribute_option_error'][0]));
        }
    }

    /**
     * Get the variant products of the selected products.
     *
     * @return array
     *   The variant products.
     */
    protected function getVariantProducts(): array {
        $variantProducts = [];
        $products = $this->getSelectedProducts();
        if ($products instanceof Collection) {
            foreach ($products as $product) {
                if ($product->hasVariants() && !empty($product->getVariantIds())) {
                    $variantProducts[$product->getId()] = $product;
                }
            }
        }

        return $variantProducts;
    }

    /**
     * Get the selected products.
     *
     * @return \Contao\Model\Collection|array
     *   The product collection or an empty array.
     */
    protected function getSelectedProducts() {
        $selectedProductIds = $this->getSessionIds();
        $products = Product::findMultipleByIds($selectedProductIds);

        if ($products instanceof Collection) {
            return $products;
        }

        return [];
    }

    /**
     * Get the price (sum) for all attribute options of the given variant product.
     *
     * @param \Isotope\Model\Product $variantProduct
     *   The variant product.
     *
     * @return float
     *   The price.
     */
    protected function getPriceForAllAttributeOptions(Product $variantProduct): float {
        $variantPrice = (float) 0;
        if ($options = $variantProduct->getOptions()) {
            foreach ($options as $name => $optionId) {
                /** @var \Isotope\Model\AttributeOption $attributeOption */
                $attributeOption = AttributeOption::findByPk($optionId);
                $variantPrice += (float) $attributeOption->{self::TL_ISO_ATTRIBUTE_OPTION_CUSTOM_PRICE_FIELD_NAME};
            }
        }

        return $variantPrice;
    }

    /**
     * Set the price for the given variant product.
     *
     * @param \Isotope\Model\Product $variantProduct
     *   The variant product.
     *
     * @return bool
     *   TRUE if successful, FALSE otherwise.
     */
    protected function setPriceForVariantProduct(Product $variantProduct, float $variantPrice): bool {
        /** @var \Isotope\Collection\ProductPrice $variantProductPrice */
        $variantProductPrices = ProductPrice::findBy('pid', $variantProduct->getId());

        // TODO: Not a nice solution, but it's done like Isotope does it in: \Isotope\Backend\Product\Price.
        $objTiers = $this->getDatabase()->query(
            'SELECT * FROM tl_iso_product_pricetier WHERE pid IN (' . implode(',', $variantProductPrices->fetchEach('id')) . ')'
        );

        $tiers = $objTiers->fetchAllAssoc();

        // TODO: At the moment only one price is possible.
        if (!empty($tiers) && isset($tiers[0]['id'])) {
           $this->getDatabase()->query('UPDATE tl_iso_product_pricetier SET price = ' . $variantPrice . ' WHERE id = ' . $tiers[0]['id']);
           return TRUE;
        }

        return FALSE;
    }

    /**
     * Log error message.
     *
     * @param string $message
     *   The error message.
     * @param string $functionName
     *   The function name.
     */
    protected function logError(string $message, string $functionName): void {
        $this->logger->log(
            LogLevel::ERROR,
            $message,
            ['contao' => new ContaoContext(__CLASS__ . '::' . $functionName, TL_GENERAL)]
        );
    }

}
