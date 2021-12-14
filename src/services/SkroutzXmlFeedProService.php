<?php
/**
 * Skroutz Xml Feed Pro plugin for Craft CMS 3.x
 *
 * @link      https://github.com/kerosin
 * @copyright Copyright (c) 2021 kerosin
 */

namespace kerosin\skroutzxmlfeedpro\services;

use kerosin\skroutzxmlfeedpro\SkroutzXmlFeedPro;
use kerosin\skroutzxmlfeedpro\models\Settings;

use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\commerce\elements\Product;
use craft\elements\db\AssetQuery;
use craft\elements\db\ElementQuery;
use craft\elements\db\UserQuery;
use craft\elements\Entry;
use craft\fields\data\OptionData;
use craft\helpers\ArrayHelper;
use craft\web\View;

use DateTime;
use Exception;

/**
 * @author    kerosin
 * @package   SkroutzXmlFeedPro
 * @since     1.0.0
 */
class SkroutzXmlFeedProService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @param mixed $criteria
     * @return Entry[]
     * @throws Exception
     */
    public function getFeedEntries($criteria = null): array
    {
        if (!empty($criteria)) {
            $result = Entry::findAll($criteria);
        } else {
            $query = Entry::find()
                ->site(Craft::$app->getSites()->getCurrentSite());

            $settings = $this->getSettings();

            if (!empty($settings->entryStatusFilter)) {
                $query->status($settings->entryStatusFilter);
            }

            if (!empty($settings->entryTypeFilter)) {
                $query->typeId($settings->entryTypeFilter);
            }

            if (!empty($settings->entryCategoryFilter)) {
                $query->relatedTo($settings->entryCategoryFilter);
            }

            $result = $query->all();
        }

        return $result;
    }

    /**
     * @param mixed $criteria
     * @return Product[]
     * @throws Exception
     */
    public function getFeedProducts($criteria = null): array
    {
        $result = [];

        if (!$this->isCommerceInstalled()) {
            return $result;
        }

        if (!empty($criteria)) {
            $result = Product::findAll($criteria);
        } else {
            $query = Product::find()
                ->site(Craft::$app->getSites()->getCurrentSite());

            $settings = $this->getSettings();

            if (!empty($settings->productStatusFilter)) {
                $query->status($settings->productStatusFilter);
            }

            if (!empty($settings->productTypeFilter)) {
                $query->typeId($settings->productTypeFilter);
            }

            if (!empty($settings->productCategoryFilter)) {
                $query->relatedTo($settings->productCategoryFilter);
            }

            if (!empty($settings->productAvailableForPurchaseFilter)) {
                $query->availableForPurchase(true);
            }

            $result = $query->all();
        }

        return $result;
    }

    /**
     * @param Element[] $elements
     * @return string
     * @throws Exception
     */
    public function getFeedXml(array $elements): string
    {
        return Craft::$app->getView()->renderTemplate(
            'skroutz-xml-feed-pro/_feed',
            [
                'elements' => $elements,
                'settings' => $this->getSettings(),
            ],
            View::TEMPLATE_MODE_CP
        );
    }

    /**
     * @param mixed $criteria
     * @return string
     * @throws Exception
     */
    public function getEntriesFeedXml($criteria = null): string
    {
        return $this->getFeedXml($this->getFeedEntries($criteria));
    }

    /**
     * @param mixed $criteria
     * @return string
     * @throws Exception
     */
    public function getProductsFeedXml($criteria = null): string
    {
        return $this->getFeedXml($this->getFeedProducts($criteria));
    }

    /**
     * @param Element[] $elements
     * @return void
     * @throws Exception
     */
    public function generateFeed(array $elements): void
    {
        $response = Craft::$app->getResponse();
        $response->getHeaders()->set('Content-Type', 'application/xml; charset=UTF-8');

        echo $this->getFeedXml($elements);
    }

    /**
     * @param Element $element
     * @param string|null $field
     * @param mixed $customValue
     * @return mixed
     * @throws Exception
     */
    public function getElementFieldValue(Element $element, ?string $field, $customValue = null)
    {
        $result = null;

        if ($field == null) {
            return $result;
        }

        if ($this->isCustomValue($field)) {
            return $customValue;
        }

        $object = $element;

        if (
            !isset($object->{$field}) &&
            $element instanceof Product &&
            isset($element->getDefaultVariant()->{$field})
        ) {
            $object = $element->getDefaultVariant();
        }

        if (!isset($object->{$field})) {
            return $result;
        }

        $value = $object->{$field};

        if ($value instanceof DateTime) {
            $result = $value->format(DateTime::ATOM);
        } elseif ($value instanceof AssetQuery) {
            $items = $value->all();

            if (count($items)) {
                $values = [];

                foreach ($items as $item) {
                    if ($item->getUrl() != null) {
                        $values[] = $item->getUrl();
                    }
                }

                if (count($values)) {
                    $result = $values;
                }
            }
        } elseif ($value instanceof UserQuery) {
            $items = $value->all();

            if (count($items)) {
                $values = [];

                foreach ($items as $item) {
                    if ($item->username != null) {
                        $values[] = $item->username;
                    }
                }

                if (count($values)) {
                    $result = $values;
                }
            }
        } elseif ($value instanceof ElementQuery) {
            $items = $value->all();

            if (count($items)) {
                $values = [];

                foreach ($items as $item) {
                    if (isset($item->title) && $item->title != '') {
                        $values[] = $item->title;
                    }
                }

                if (count($values)) {
                    $result = $values;
                }
            }
        } elseif (ArrayHelper::isTraversable($value)) {
            if (count($value)) {
                $values = [];

                foreach ($value as $item) {
                    if ($item instanceof OptionData && isset($item->label) && $item->label != '') {
                        $values[] = $item->label;
                    } elseif ($item != null) {
                        $values[] = (string)$item;
                    }
                }

                if (count($values)) {
                    $result = $values;
                }
            }
        } elseif ($value instanceof OptionData) {
            if (isset($value->label) && $value->label != '') {
                $result = $value->label;
            } else {
                $result = (string)$value;
            }
        } else {
            $result = $value;
        }

        return $result;
    }

    /**
     * @param Element $element
     * @return mixed
     * @throws Exception
     */
    public function getElementInstockFieldValue(Element $element)
    {
        $settings = $this->getSettings();
        $result = $settings::INSTOCK_IN_STOCK;

        if (
            $this->isUseStockField($settings->instockField) &&
            $this->isCommerceInstalled()
        ) {
            if ($element instanceof Product) {
                $result = $element->getDefaultVariant()->hasStock()
                    ? $settings::INSTOCK_IN_STOCK
                    : $settings::INSTOCK_OUT_OF_STOCK;
            }
        } elseif ($settings->instockField != null) {
            $result = $this->getElementFieldValue(
                $element,
                $settings->instockField,
                $settings->instockCustomValue
            ) ?: $settings::INSTOCK_IN_STOCK;
        }

        return $result;
    }

    /**
     * @param Element $element
     * @return array
     * @throws Exception
     */
    public function getElementColors(Element $element): array
    {
        $result = [];
        $settings = $this->getSettings();

        if ($settings->colorField == null) {
            return $result;
        }

        if (
            $element instanceof Product &&
            $element->getType()->hasVariants && (
                $this->hasField($element->getDefaultVariant(), 'colorField') ||
                $this->hasField($element->getDefaultVariant(), 'sizeField')
            )
        ) {
            $defaultVariant = $element->getDefaultVariant();
            $isUseStockField = $this->isUseStockField($settings->instockField);
            $hasInstock = $this->hasField($defaultVariant, 'instockField');
            $hasStock = $isUseStockField || $hasInstock;
            $hasColor = $this->hasField($defaultVariant, 'colorField');
            $hasPrice = $this->hasField($defaultVariant, 'priceField');
            $hasAvailability = $this->hasField($defaultVariant, 'availabilityField');
            $hasMpn = $this->hasField($defaultVariant, 'mpnField');
            $hasEan = $this->hasField($defaultVariant, 'eanField');
            $hasSize = $this->hasField($defaultVariant, 'sizeField');
            $hasWeight = $this->hasField($defaultVariant, 'weightField');
            $defaultVariantPrice = $hasPrice ? $this->getElementFieldValue($defaultVariant, $settings->priceField) : null;

            if ($hasColor) {
                foreach ($element->getVariants() as $variant) {
                    $stock = $settings::INSTOCK_IN_STOCK;

                    if ($isUseStockField) {
                        $stock = $variant->hasStock() ? $settings::INSTOCK_IN_STOCK : $settings::INSTOCK_OUT_OF_STOCK;
                    } elseif ($hasInstock) {
                        $stock = $this->getElementFieldValue($variant, $settings->instockField) ?: $settings::INSTOCK_IN_STOCK;
                    }

                    if ($this->isSkipOutOfStockVariants($stock)) {
                        continue;
                    }

                    $price = $hasPrice ? $this->getElementFieldValue($variant, $settings->priceField) : null;
                    $availability = $hasAvailability ? $this->getElementFieldValue($variant, $settings->availabilityField) : null;
                    $mpn = $hasMpn ? $this->getElementFieldValue($variant, $settings->mpnField) : null;
                    $ean = $hasEan ? $this->getElementFieldValue($variant, $settings->eanField) : null;
                    $size = $hasSize ? $this->getElementFieldValue($variant, $settings->sizeField) : null;
                    $weight = $hasWeight ? $this->getElementFieldValue($variant, $settings->weightField) : null;

                    $colors = $this->getElementFieldValue($variant, $settings->colorField) ?: '';
                    $colors = array_unique((array)$colors);

                    foreach ($colors as $color) {
                        if (!isset($result[$color])) {
                            $result[$color] = [];
                        }

                        if (
                            $hasStock &&
                            (
                                !isset($result[$color]['stock']) ||
                                $result[$color]['stock'] == $settings::INSTOCK_OUT_OF_STOCK
                            )
                        ) {
                            $result[$color]['stock'] = $stock;
                        }

                        if ($hasPrice) {
                            if (!isset($result[$color]['prices'])) {
                                $result[$color]['prices'] = [];
                                $result[$color]['price'] = $this->isVariantPriceTypeDefault() ? $defaultVariantPrice : null;
                            }

                            if ($price != null) {
                                $result[$color]['prices'][] = $price;
                            }
                        }

                        if ($hasAvailability && !isset($result[$color]['availability'])) {
                            $result[$color]['availability'] = $availability;
                        }

                        if ($hasMpn && !isset($result[$color]['mpn'])) {
                            $result[$color]['mpn'] = $mpn;
                        }

                        if ($hasEan && !isset($result[$color]['ean'])) {
                            $result[$color]['ean'] = $ean;
                        }

                        if ($hasSize) {
                            if (!isset($result[$color]['sizes'])) {
                                $result[$color]['sizes'] = [];
                            }

                            if ($size != null) {
                                $result[$color]['sizes'] = array_unique(array_merge($result[$color]['sizes'], (array)$size));
                            }
                        }

                        if ($hasWeight && !isset($result[$color]['weight'])) {
                            $result[$color]['weight'] = $weight;
                        }
                    }
                }
            } elseif ($hasSize) {
                $stock = $settings::INSTOCK_IN_STOCK;

                if ($isUseStockField) {
                    $stock = $defaultVariant->hasStock() ? $settings::INSTOCK_IN_STOCK : $settings::INSTOCK_OUT_OF_STOCK;
                } elseif ($hasInstock) {
                    $stock = $this->getElementFieldValue($defaultVariant, $settings->instockField) ?: $settings::INSTOCK_IN_STOCK;
                }

                $availability = $hasAvailability ? $this->getElementFieldValue($defaultVariant, $settings->availabilityField) : null;
                $mpn = $hasMpn ? $this->getElementFieldValue($defaultVariant, $settings->mpnField) : null;
                $ean = $hasEan ? $this->getElementFieldValue($defaultVariant, $settings->eanField) : null;
                $weight = $hasWeight ? $this->getElementFieldValue($defaultVariant, $settings->weightField) : null;
                $prices = [];
                $sizes = [];

                foreach ($element->getVariants() as $variant) {
                    $variantStock = $settings::INSTOCK_IN_STOCK;

                    if ($isUseStockField) {
                        $variantStock = $variant->hasStock() ? $settings::INSTOCK_IN_STOCK : $settings::INSTOCK_OUT_OF_STOCK;
                    } elseif ($hasInstock) {
                        $variantStock = $this->getElementFieldValue($variant, $settings->instockField) ?: $settings::INSTOCK_IN_STOCK;
                    }

                    if ($this->isSkipOutOfStockVariants($variantStock)) {
                        continue;
                    }

                    if ($hasStock && ($stock == null || $stock == $settings::INSTOCK_OUT_OF_STOCK)) {
                        $stock = $variantStock;
                    }

                    if ($hasPrice) {
                        $price = $this->getElementFieldValue($variant, $settings->priceField);

                        if ($price != null) {
                            $prices[] = $price;
                        }
                    }

                    if ($hasAvailability && $availability == null) {
                        $availability = $this->getElementFieldValue($variant, $settings->availabilityField);
                    }

                    if ($hasMpn && $mpn == null) {
                        $mpn = $this->getElementFieldValue($variant, $settings->mpnField);
                    }

                    if ($hasEan && $ean == null) {
                        $ean = $this->getElementFieldValue($variant, $settings->eanField);
                    }

                    $size = $this->getElementFieldValue($variant, $settings->sizeField);

                    if ($size != null) {
                        $sizes = array_merge($sizes, (array)$size);
                    }

                    if ($hasWeight && $weight == null) {
                        $weight = $this->getElementFieldValue($variant, $settings->weightField);
                    }
                }

                $sizes = array_unique($sizes);
                $colors = $this->getElementFieldValue($element, $settings->colorField) ?: '';
                $colors = array_unique((array)$colors);

                foreach ($colors as $color) {
                    $result[$color] = ['sizes' => $sizes];

                    if ($hasStock) {
                        $result[$color]['stock'] = $stock;
                    }

                    if ($hasPrice) {
                        $result[$color]['prices'] = $prices;
                        $result[$color]['price'] = $this->isVariantPriceTypeDefault() ? $defaultVariantPrice : null;
                    }

                    if ($hasAvailability) {
                        $result[$color]['availability'] = $availability;
                    }

                    if ($hasMpn) {
                        $result[$color]['mpn'] = $mpn;
                    }

                    if ($hasEan) {
                        $result[$color]['ean'] = $ean;
                    }

                    if ($hasWeight) {
                        $result[$color]['weight'] = $weight;
                    }
                }
            }

            if ($hasPrice && !$this->isVariantPriceTypeDefault()) {
                $items = $result;

                foreach ($items as $key => $item) {
                    if (empty($item['prices'])) {
                        continue;
                    }

                    switch ($settings->variantPriceType) {
                        case $settings::VARIANT_PRICE_TYPE_MIN_PRICE:
                            $result[$key]['price'] = min(array_filter($item['prices']));
                            break;
                        case $settings::VARIANT_PRICE_TYPE_MAX_PRICE:
                            $result[$key]['price'] = max(array_filter($item['prices']));
                            break;
                        case $settings::VARIANT_PRICE_TYPE_AVG_PRICE:
                            $prices = array_filter($item['prices']);

                            if (count($prices) > 0) {
                                $result[$key]['price'] = array_sum($prices) / count($prices);
                            }

                            break;
                    }
                }
            }
        } else {
            $colors = $this->getElementFieldValue($element, $settings->colorField);
            $sizes = $this->getElementFieldValue($element, $settings->sizeField);

            if ($colors != null) {
                $colors = array_unique((array)$colors);

                foreach ($colors as $color) {
                    if (!isset($result[$color])) {
                        $result[$color] = ['sizes' => []];
                    }

                    if ($sizes != null) {
                        $result[$color]['sizes'] = array_unique((array)$sizes);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isCustomValue(?string $value): bool
    {
        return $value == $this->getSettings()::OPTION_CUSTOM_VALUE;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isUseWeightUnit(?string $value): bool
    {
        return $value == $this->getSettings()::OPTION_USE_WEIGHT_UNIT;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isUseInstock(?string $value): bool
    {
        return $value == $this->getSettings()::OPTION_USE_INSTOCK;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isUseStockField(?string $value): bool
    {
        return $value == $this->getSettings()::OPTION_USE_STOCK_FIELD;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isElementInStock(?string $value): bool
    {
        return $value == null || $value == $this->getSettings()::INSTOCK_IN_STOCK;
    }

    /**
     * @param Element $element
     * @param string $field
     * @return bool
     */
    public function hasField(Element $element, string $field): bool
    {
        $settings = $this->getSettings();

        return $settings->{$field} != null && isset($element->{$settings->{$field}});
    }

    /**
     * @return bool
     */
    public function isVariantPriceTypeDefault(): bool
    {
        $settings = $this->getSettings();

        return $settings->variantPriceType == $settings::VARIANT_PRICE_TYPE_DEFAULT;
    }

    /**
     * @param string|null $stock
     * @return bool
     */
    public function isSkipOutOfStockVariants(string $stock = null): bool
    {
        return !$this->getSettings()->includeOutOfStockVariants && !$this->isElementInStock($stock);
    }

    /**
     * @return bool
     * @since 1.2.0
     */
    public function isCommerceInstalled(): bool
    {
        return Craft::$app->getPlugins()->isPluginInstalled('commerce');
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return Settings
     * @since 1.2.0
     */
    protected function getSettings(): Settings
    {
        return SkroutzXmlFeedPro::$plugin->getSettings();
    }
}
