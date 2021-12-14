<?php
/**
 * Skroutz Xml Feed Pro plugin for Craft CMS 3.x
 *
 * @link      https://github.com/kerosin
 * @copyright Copyright (c) 2021 kerosin
 */

namespace kerosin\skroutzxmlfeedpro\variables;

use kerosin\skroutzxmlfeedpro\SkroutzXmlFeedPro;
use kerosin\skroutzxmlfeedpro\services\SkroutzXmlFeedProService;

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
        $this->getService()->generateFeed($elements);
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
        return $this->getService()->getElementFieldValue($element, $field, $customValue);
    }

    /**
     * @param Element $element
     * @return mixed
     * @throws Exception
     */
    public function elementInstockFieldValue(Element $element)
    {
        return $this->getService()->getElementInstockFieldValue($element);
    }

    /**
     * @param Element $element
     * @return array
     * @throws Exception
     */
    public function elementColors(Element $element): array
    {
        return $this->getService()->getElementColors($element);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isCustomValue(?string $value): bool
    {
        return $this->getService()->isCustomValue($value);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isUseWeightUnit(?string $value): bool
    {
        return $this->getService()->isUseWeightUnit($value);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isUseInstock(?string $value): bool
    {
        return $this->getService()->isUseInstock($value);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isUseStockField(?string $value): bool
    {
        return $this->getService()->isUseStockField($value);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public function isElementInStock(?string $value): bool
    {
        return $this->getService()->isElementInStock($value);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return SkroutzXmlFeedProService
     * @since 1.2.0
     */
    protected function getService(): SkroutzXmlFeedProService
    {
        return SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService;
    }
}
