# Skroutz Xml Feed Pro plugin for Craft CMS 3.x

Skroutz Xml Feed for Craft CMS.

![Plugin Icon](resources/img/plugin-icon.svg)

## License

This plugin requires a commercial license purchasable through the [Craft Plugin Store](https://plugins.craftcms.com/craft-recaptcha-pro).

## Requirements

This plugin requires Craft CMS 3.1.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require kerosin/skroutz-xml-feed-pro

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Skroutz Xml Feed Pro.

## Configuration

Open the Craft admin and go to Settings → Plugins → Skroutz Xml Feed Pro → Settings.

## Usage

### Entries feed url

    http://example.com/skroutz-xml-feed-pro/feed/entries
    
To save the output to a file add `save` query param to the url:

    http://example.com/skroutz-xml-feed-pro/feed/entries?save
    
The file will be saved in the following path:

    http://example.com/skroutz-xml-feed-pro/entries-SITE_HANDLE.xml
    
Replace `SITE_HANDLE` in the url with a real site handle.
    
### Products feed url

    http://example.com/skroutz-xml-feed-pro/feed/products
    
To save the output to a file add `save` query param to the url:

    http://example.com/skroutz-xml-feed-pro/feed/products?save
    
The file will be saved in the following path:

    http://example.com/skroutz-xml-feed-pro/products-SITE_HANDLE.xml
    
Replace `SITE_HANDLE` in the url with a real site handle.
    
### Twig function

```twig
{% set entries = craft.entries.section('foo').all() %}
{{ craft.skroutzXmlFeedPro.generateFeed(entries) }}
```

```twig
{% set products = craft.products.all() %}
{{ craft.skroutzXmlFeedPro.generateFeed(products) }}
```

---

Brought to you by [kerosin](https://github.com/kerosin)
