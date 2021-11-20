<?php
/**
 * Skroutz Xml Feed Pro plugin for Craft CMS 3.x
 *
 * @link      https://github.com/kerosin
 * @copyright Copyright (c) 2021 kerosin
 */

namespace kerosin\skroutzxmlfeedpro\variables;

use kerosin\skroutzxmlfeedpro\SkroutzXmlFeedPro;

use craft\base\Element;

use Exception;

/**
 * @author    kerosin
 * @package   SkroutzXmlFeedPro
 * @since     1.0.0
 */
class SkroutzXmlFeedProVariable
{
    // Public Methods
    // =========================================================================

    /**
     * @param Element[] $elements
     * @return void
     * @throws Exception
     */
    public function generateFeed(array $elements): void
    {
        SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService->generateFeed($elements);
    }

    /**
     * @param Element $element
     * @param string|null $field
     * @param mixed $customValue
     * @return mixed
     * @throws Exception
     */
    public function elementFieldValue(Element $element, ?string $field, $customValue = null)
    {
        return SkroutzXmlFeedPro::$plugin
            ->skroutzXmlFeedProService
            ->getElementFieldValue($element, $field, $customValue);
    }

    /**
     * @param Element $element
     * @return mixed
     * @throws Exception
     */
    public function elementInstockFieldValue(Element $element)
    {
        return SkroutzXmlFeedPro::$plugin
            ->skroutzXmlFeedProService
            ->getElementInstockFieldValue($element);
    }

    /**
     * @param Element $element
     * @return array
     * @throws Exception
     */
    public function elementColors(Element $element): array
    {
        return SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService->getElementColors($element);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isCustomValue(?string $value): bool
    {
        return SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService->isCustomValue($value);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isUseWeightUnit(?string $value): bool
    {
        return SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService->isUseWeightUnit($value);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isUseInstock(?string $value): bool
    {
        return SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService->isUseInstock($value);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isUseStockField(?string $value): bool
    {
        return SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService->isUseStockField($value);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isElementInStock(?string $value): bool
    {
        return SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService->isElementInStock($value);
    }
}
