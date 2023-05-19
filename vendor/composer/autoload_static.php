<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7178a58a8363608f3b54367b58c9a8ec
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Payjp\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Payjp\\' => 
        array (
            0 => __DIR__ . '/..' . '/payjp/payjp-php/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7178a58a8363608f3b54367b58c9a8ec::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7178a58a8363608f3b54367b58c9a8ec::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7178a58a8363608f3b54367b58c9a8ec::$classMap;

        }, null, ClassLoader::class);
    }
}
