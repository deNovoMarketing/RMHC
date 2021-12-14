<?php
namespace BooklyCustomDuration\Lib;

use Bookly\Lib;
use BooklyCustomDuration\Backend;
use BooklyCustomDuration\Frontend;

/**
 * Class Plugin
 * @package BooklyBlank\Lib
 */
abstract class Plugin extends Lib\Base\Plugin
{
    protected static $prefix;
    protected static $title;
    protected static $version;
    protected static $slug;
    protected static $directory;
    protected static $main_file;
    protected static $basename;
    protected static $text_domain;
    protected static $root_namespace;
    protected static $embedded;

    /**
     * @inheritdoc
     */
    protected static function init()
    {
        // Init proxy.
        Backend\Components\Dialogs\Service\Edit\ProxyProviders\Local::init();
        Backend\Components\Dialogs\Service\Edit\ProxyProviders\Shared::init();
        Backend\Components\TinyMce\ProxyProviders\Shared::init();
        Backend\Modules\Appearance\ProxyProviders\Local::init();
        Backend\Modules\Appearance\ProxyProviders\Shared::init();

        Frontend\Modules\Booking\ProxyProviders\Shared::init();

        ProxyProviders\Shared::init();
    }
}