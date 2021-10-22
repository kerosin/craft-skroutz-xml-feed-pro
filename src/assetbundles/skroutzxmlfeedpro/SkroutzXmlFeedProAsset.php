<?php
/**
 * Skroutz Xml Feed Pro plugin for Craft CMS 3.x
 *
 * @link      https://github.com/kerosin
 * @copyright Copyright (c) 2021 kerosin
 */

namespace kerosin\skroutzxmlfeedpro\assetbundles\skroutzxmlfeedpro;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    kerosin
 * @package   SkroutzXmlFeedPro
 * @since     1.0.0
 */
class SkroutzXmlFeedProAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        $this->sourcePath = '@kerosin/skroutzxmlfeedpro/assetbundles/skroutzxmlfeedpro/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/app.css',
        ];

        parent::init();
    }
}
