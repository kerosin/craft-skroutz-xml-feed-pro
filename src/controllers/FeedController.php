<?php
/**
 * Skroutz Xml Feed Pro plugin for Craft CMS 3.x
 *
 * @link      https://github.com/kerosin
 * @copyright Copyright (c) 2021 kerosin
 */

namespace kerosin\skroutzxmlfeedpro\controllers;

use kerosin\skroutzxmlfeedpro\SkroutzXmlFeedPro;
use kerosin\skroutzxmlfeedpro\services\SkroutzXmlFeedProService;

use Craft;
use craft\helpers\FileHelper;
use craft\web\Controller;

use yii\web\Response;

use Exception;

/**
 * @author    kerosin
 * @package   SkroutzXmlFeedPro
 * @since     1.0.0
 */
class FeedController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * Allows anonymous access to this controller's actions.
     *
     * @var bool|array
     */
    protected $allowAnonymous = ['entries', 'products'];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     * @throws Exception
     */
    public function actionEntries()
    {
        $this->setXmlResponseHeader();

        $output = $this->getService()->getEntriesFeedXml();

        if ($this->isSaveXmlToFile()) {
            $this->saveXmlToFile($output, 'entries');
        }

        return $output;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function actionProducts()
    {
        $this->setXmlResponseHeader();

        $output = $this->getService()->getProductsFeedXml();

        if ($this->isSaveXmlToFile()) {
            $this->saveXmlToFile($output, 'products');
        }

        return $output;
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

    /**
     * @return void
     * @since 1.3.0
     */
    protected function setXmlResponseHeader(): void
    {
        $response = Craft::$app->getResponse();
        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @param string $xml
     * @param string|null $filePrefix
     * @return void
     * @throws Exception
     * @since 1.3.0
     */
    protected function saveXmlToFile(string $xml, ?string $filePrefix = null): void
    {
        $path = Craft::getAlias('@webroot') . '/skroutz-xml-feed-pro/';
        $path .= $filePrefix != null ? $filePrefix . '-' : '';
        $path .= Craft::$app->getSites()->getCurrentSite()->handle . '.xml';

        FileHelper::writeToFile($path, $xml);
    }

    /**
     * @return bool
     * @since 1.3.0
     */
    protected function isSaveXmlToFile(): bool
    {
        return Craft::$app->getRequest()->getQueryParam('save') !== null;
    }
}
