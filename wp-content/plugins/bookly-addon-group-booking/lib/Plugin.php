<?php
namespace BooklyGroupBooking\Lib;

use Bookly\Lib as BooklyLib;
use BooklyGroupBooking\Backend;
use BooklyGroupBooking\Frontend;

/**
 * Class Plugin
 * @package BooklyGroupBooking\Lib
 */
abstract class Plugin extends BooklyLib\Base\Plugin
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
        Backend\Components\TinyMce\ProxyProviders\Local::init();
        Backend\Components\TinyMce\ProxyProviders\Shared::init();
        Backend\Components\Dialogs\Service\Edit\ProxyProviders\Local::init();
        Backend\Components\Dialogs\Staff\Edit\ProxyProviders\Shared::init();
        Backend\Modules\Appearance\ProxyProviders\Local::init();
        Backend\Modules\Appearance\ProxyProviders\Shared::init();
        Backend\Modules\Notifications\ProxyProviders\Shared::init();
        Backend\Modules\Settings\ProxyProviders\Shared::init();
        Frontend\Modules\Booking\ProxyProviders\Local::init();
        Frontend\Modules\Booking\ProxyProviders\Shared::init();
        ProxyProviders\Shared::init();
    }
}