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

        return $this->getService()->getEntriesFeedXml();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function actionProducts()
    {
        $this->setXmlResponseHeader();

        return $this->getService()->getProductsFeedXml();
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
}
