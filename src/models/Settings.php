<?php
/**
 * Skroutz Xml Feed Pro plugin for Craft CMS 3.x
 *
 * @link      https://github.com/kerosin
 * @copyright Copyright (c) 2021 kerosin
 */

namespace kerosin\skroutzxmlfeedpro\models;

use Craft;
use craft\base\Model;
use craft\commerce\errors\CurrencyException;
use craft\commerce\Plugin as CommercePlugin;

/**
 * @author    kerosin
 * @package   SkroutzXmlFeedPro
 * @since     1.0.0
 */
class Settings extends Model
{
    // Constants
    // =========================================================================

    const VARIANT_PRICE_TYPE_DEFAULT = 1;
    const VARIANT_PRICE_TYPE_MIN_PRICE = 2;
    const VARIANT_PRICE_TYPE_MAX_PRICE = 3;
    const VARIANT_PRICE_TYPE_AVG_PRICE = 4;

    const OPTION_CUSTOM_VALUE = '__custom_value__';
    const OPTION_USE_WEIGHT_UNIT = '__use_weight_unit__';
    const OPTION_USE_INSTOCK = '__use_instock__';
    const OPTION_USE_STOCK_FIELD = '__use_stock_field__';

    const INSTOCK_IN_STOCK = 'Y';
    const INSTOCK_OUT_OF_STOCK = 'N';

    // Public Properties
    // =========================================================================

    /**
     * Tax rate.
     *
     * @var float
     */
    public $taxRate;

    /**
     * Currency.
     *
     * @var string
     */
    public $currency;

    /**
     * Include out of stock elements.
     *
     * @var bool
     */
    public $includeOutOfStockElements = false;

    /**
     * Append color to ID.
     *
     * @var bool
     */
    public $appendColorToId = true;

    /**
     * Append color to name.
     *
     * @var bool
     */
    public $appendColorToName = true;

    /**
     * Append color to url.
     *
     * @var bool
     */
    public $appendColorToUrl = true;

    /**
     * Include out of stock variants.
     *
     * @var bool
     */
    public $includeOutOfStockVariants = false;

    /**
     * Variant price type.
     *
     * @var int
     */
    public $variantPriceType = self::VARIANT_PRICE_TYPE_DEFAULT;

    /**
     * UID [uid] field.
     *
     * @var string
     */
    public $uidField;

    /**
     * Name [name] field.
     *
     * @var string
     */
    public $nameField;

    /**
     * Image [image] field.
     *
     * @var string
     */
    public $imageField;

    /**
     * Additional image [additional_image] field.
     *
     * @var string
     */
    public $additionalImageField;

    /**
     * Category [category] field.
     *
     * @var string
     */
    public $categoryField;

    /**
     * Price [price] field.
     *
     * @var string
     */
    public $priceField;

    /**
     * Availability [availability] field.
     *
     * @var string
     */
    public $availabilityField;

    /**
     * Availability custom value.
     *
     * @var string
     */
    public $availabilityCustomValue;

    /**
     * Availability in stock value.
     *
     * @var string
     */
    public $availabilityInStockValue;

    /**
     * Availability out of stock value.
     *
     * @var string
     */
    public $availabilityOutOfStockValue;

    /**
     * Manufacturer [manufacturer] field.
     *
     * @var string
     */
    public $manufacturerField;

    /**
     * Manufacturer custom value.
     *
     * @var string
     */
    public $manufacturerCustomValue;

    /**
     * MPN [mpn] field.
     *
     * @var string
     */
    public $mpnField;

    /**
     * EAN [ean] field.
     *
     * @var string
     */
    public $eanField;

    /**
     * Size [size] field.
     *
     * @var string
     */
    public $sizeField;

    /**
     * Weight [weight] field.
     *
     * @var string
     */
    public $weightField;

    /**
     * Weight unit field.
     *
     * @var string
     */
    public $weightUnitField;

    /**
     * Weight unit custom value.
     *
     * @var string
     */
    public $weightUnitCustomValue;

    /**
     * Instock [instock] field.
     *
     * @var string
     */
    public $instockField;

    /**
     * Instock custom value.
     *
     * @var string
     */
    public $instockCustomValue;

    /**
     * Shipping [shipping] field.
     *
     * @var string
     */
    public $shippingField;

    /**
     * Shipping custom value.
     *
     * @var string
     */
    public $shippingCustomValue;

    /**
     * Color [color] field.
     *
     * @var string
     */
    public $colorField;

    /**
     * Description [description] field.
     *
     * @var string
     */
    public $descriptionField;

    // Public Methods
    // =========================================================================

    /**
     * @return array
     */
    public static function getCmsStandardFields(): array
    {
        return [
            'id' => Craft::t('skroutz-xml-feed-pro', 'ID'),
            'title' => Craft::t('skroutz-xml-feed-pro', 'Title'),
            'expiryDate' => Craft::t('skroutz-xml-feed-pro', 'Expiry Date'),
        ];
    }

    /**
     * @return array
     */
    public static function getCommerceStandardFields(): array
    {
        return [
            'sku' => Craft::t('skroutz-xml-feed-pro', 'SKU'),
            'price' => Craft::t('skroutz-xml-feed-pro', 'Price'),
            'salePrice' => Craft::t('skroutz-xml-feed-pro', 'Sale Price'),
            'length' => Craft::t('skroutz-xml-feed-pro', 'Dimensions (L)'),
            'width' => Craft::t('skroutz-xml-feed-pro', 'Dimensions (W)'),
            'height' => Craft::t('skroutz-xml-feed-pro', 'Dimensions (H)'),
            'weight' => Craft::t('skroutz-xml-feed-pro', 'Weight'),
        ];
    }

    /**
     * @return array
     */
    public function getStandardFields(): array
    {
        $result = self::getCmsStandardFields();

        if (Craft::$app->getPlugins()->isPluginInstalled('commerce')) {
            $result = array_merge($result, self::getCommerceStandardFields());
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCustomFields(): array
    {
        $result = [];

        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            $result[$field->handle] = $field->name;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getFieldOptions(): array
    {
        $result = [];
        $fields = $this->getStandardFields();

        if (count($fields)) {
            $result[] = ['optgroup' => Craft::t('skroutz-xml-feed-pro', 'Standard Fields')];

            foreach ($fields as $handle => $name) {
                $result[] = [
                    'value' => $handle,
                    'label' => $name,
                ];
            }
        }

        $fields = $this->getCustomFields();

        if (count($fields)) {
            $result[] = ['optgroup' => Craft::t('skroutz-xml-feed-pro', 'Custom Fields')];

            foreach ($fields as $handle => $name) {
                $result[] = [
                    'value' => $handle,
                    'label' => $name,
                ];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCurrencyOptions(): array
    {
        $result = [];

        if (!Craft::$app->getPlugins()->isPluginInstalled('commerce')) {
            return $result;
        }

        try {
            $currencies = CommercePlugin::getInstance()->getPaymentCurrencies()->getAllPaymentCurrencies();

            foreach ($currencies as $currency) {
                $result[] = [
                    'value' => $currency->iso,
                    'label' => $currency->iso,
                ];
            }
        } catch (CurrencyException $e) {
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getVariantPriceTypeOptions(): array
    {
        return [
            self::VARIANT_PRICE_TYPE_DEFAULT => Craft::t('skroutz-xml-feed-pro', 'Default Variant'),
            self::VARIANT_PRICE_TYPE_MIN_PRICE => Craft::t('skroutz-xml-feed-pro', 'Minimum Price'),
            self::VARIANT_PRICE_TYPE_MAX_PRICE => Craft::t('skroutz-xml-feed-pro', 'Maximum Price'),
            self::VARIANT_PRICE_TYPE_AVG_PRICE => Craft::t('skroutz-xml-feed-pro', 'Average Price'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $fieldOptions = array_merge(
            [
                self::OPTION_CUSTOM_VALUE,
                self::OPTION_USE_WEIGHT_UNIT,
                self::OPTION_USE_INSTOCK,
                self::OPTION_USE_STOCK_FIELD,
            ],
            array_keys($this->getStandardFields()),
            array_keys($this->getCustomFields())
        );

        return [
            ['taxRate', 'number'],
            ['currency', 'in', 'range' => array_keys($this->getCurrencyOptions())],
            ['includeOutOfStockElements', 'boolean'],
            ['appendColorToId', 'boolean'],
            ['appendColorToName', 'boolean'],
            ['appendColorToUrl', 'boolean'],
            ['includeOutOfStockVariants', 'boolean'],
            ['variantPriceType', 'in', 'range' => array_keys($this->getVariantPriceTypeOptions())],
            ['uidField', 'in', 'range' => $fieldOptions],
            ['nameField', 'in', 'range' => $fieldOptions],
            ['imageField', 'in', 'range' => $fieldOptions],
            ['additionalImageField', 'in', 'range' => $fieldOptions],
            ['categoryField', 'in', 'range' => $fieldOptions],
            ['priceField', 'in', 'range' => $fieldOptions],
            ['availabilityField', 'in', 'range' => $fieldOptions],
            ['manufacturerField', 'in', 'range' => $fieldOptions],
            ['mpnField', 'in', 'range' => $fieldOptions],
            ['eanField', 'in', 'range' => $fieldOptions],
            ['sizeField', 'in', 'range' => $fieldOptions],
            ['weightField', 'in', 'range' => $fieldOptions],
            ['weightUnitField', 'in', 'range' => $fieldOptions],
            ['instockField', 'in', 'range' => $fieldOptions],
            ['shippingField', 'in', 'range' => $fieldOptions],
            ['colorField', 'in', 'range' => $fieldOptions],
            ['descriptionField', 'in', 'range' => $fieldOptions],
        ];
    }
}
