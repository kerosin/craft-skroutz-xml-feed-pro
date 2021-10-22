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
        $response = Craft::$app->getResponse();
        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', 'application/xml; charset=UTF-8');

        /** @var SkroutzXmlFeedProService $skroutzXmlFeedProService */
        $skroutzXmlFeedProService = SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService;

        return $skroutzXmlFeedProService->getEntriesFeedXml();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function actionProducts()
    {
        $response = Craft::$app->getResponse();
        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', 'application/xml; charset=UTF-8');

        /** @var SkroutzXmlFeedProService $skroutzXmlFeedProService */
        $skroutzXmlFeedProService = SkroutzXmlFeedPro::$plugin->skroutzXmlFeedProService;

        return $skroutzXmlFeedProService->getProductsFeedXml();
    }
}
