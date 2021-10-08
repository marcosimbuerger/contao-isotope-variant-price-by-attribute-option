# Contao Isotope Variant Price By Attribute Option Generator

## Initial situation

There was a need to generate the price of variant products automatically and according to the defined attribute option.
Imagine a setup with multiple attribute options, which generates a lot of product variants.
There may be too many variants to set the price manually. That is why this module was created.
It currently offers only the possibility to save one fixed price on a product variant.

## Usage

1. Install
```bash
composer require marcosimbuerger/contao-isotope-variant-price-by-attribute-option
```

2. Update the database via Contao install tool.
3. Create an Isotope attribute, which has options (e.g. select menu).
4. Add options to this attribute. Set a price on each option.
5. Create an Isotope product type, activate 'variants' and add the product price and your custom attributes as variant attributes.
6. Go to the Isotope product overview and create products with variants.
7. On the Isotope product overview select the desired products and click on the according action button to generate the variant prices by its attribute options.

   Important: This action changes the price of the variants in the database. The change cannot be undone!

