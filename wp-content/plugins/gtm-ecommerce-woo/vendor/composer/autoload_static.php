<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit756ac87c04f28c62893b9c6fa457d43b
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GtmEcommerceWoo\\Lib\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GtmEcommerceWoo\\Lib\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'GtmEcommerceWoo\\Lib\\Container' => __DIR__ . '/../..' . '/src/Container.php',
        'GtmEcommerceWoo\\Lib\\EventStrategy\\AbstractEventStrategy' => __DIR__ . '/../..' . '/src/EventStrategy/AbstractEventStrategy.php',
        'GtmEcommerceWoo\\Lib\\EventStrategy\\AddToCartStrategy' => __DIR__ . '/../..' . '/src/EventStrategy/AddToCartStrategy.php',
        'GtmEcommerceWoo\\Lib\\EventStrategy\\PurchaseStrategy' => __DIR__ . '/../..' . '/src/EventStrategy/PurchaseStrategy.php',
        'GtmEcommerceWoo\\Lib\\GaEcommerceEntity\\Event' => __DIR__ . '/../..' . '/src/GaEcommerceEntity/Event.php',
        'GtmEcommerceWoo\\Lib\\GaEcommerceEntity\\Item' => __DIR__ . '/../..' . '/src/GaEcommerceEntity/Item.php',
        'GtmEcommerceWoo\\Lib\\Service\\EventInspectorService' => __DIR__ . '/../..' . '/src/Service/EventInspectorService.php',
        'GtmEcommerceWoo\\Lib\\Service\\EventStrategiesService' => __DIR__ . '/../..' . '/src/Service/EventStrategiesService.php',
        'GtmEcommerceWoo\\Lib\\Service\\GtmSnippetService' => __DIR__ . '/../..' . '/src/Service/GtmSnippetService.php',
        'GtmEcommerceWoo\\Lib\\Service\\OrderMonitorService' => __DIR__ . '/../..' . '/src/Service/OrderMonitorService.php',
        'GtmEcommerceWoo\\Lib\\Service\\PluginService' => __DIR__ . '/../..' . '/src/Service/PluginService.php',
        'GtmEcommerceWoo\\Lib\\Service\\ProductFeedService' => __DIR__ . '/../..' . '/src/Service/ProductFeedService.php',
        'GtmEcommerceWoo\\Lib\\Service\\SettingsService' => __DIR__ . '/../..' . '/src/Service/SettingsService.php',
        'GtmEcommerceWoo\\Lib\\Util\\OrderMonitorTrait' => __DIR__ . '/../..' . '/src/Util/OrderMonitorTrait.php',
        'GtmEcommerceWoo\\Lib\\Util\\OrderWrapper' => __DIR__ . '/../..' . '/src/Util/OrderWrapper.php',
        'GtmEcommerceWoo\\Lib\\Util\\SanitizationUtil' => __DIR__ . '/../..' . '/src/Util/SanitizationUtil.php',
        'GtmEcommerceWoo\\Lib\\Util\\WcOutputUtil' => __DIR__ . '/../..' . '/src/Util/WcOutputUtil.php',
        'GtmEcommerceWoo\\Lib\\Util\\WcTransformerUtil' => __DIR__ . '/../..' . '/src/Util/WcTransformerUtil.php',
        'GtmEcommerceWoo\\Lib\\Util\\WooCommerceFeaturesUtil' => __DIR__ . '/../..' . '/src/Util/WooCommerceFeaturesUtil.php',
        'GtmEcommerceWoo\\Lib\\Util\\WpSettingsUtil' => __DIR__ . '/../..' . '/src/Util/WpSettingsUtil.php',
        'GtmEcommerceWoo\\Lib\\ValueObject\\OrderMonitorStatistics' => __DIR__ . '/../..' . '/src/ValueObject/OrderMonitorStatistics.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit756ac87c04f28c62893b9c6fa457d43b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit756ac87c04f28c62893b9c6fa457d43b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit756ac87c04f28c62893b9c6fa457d43b::$classMap;

        }, null, ClassLoader::class);
    }
}
