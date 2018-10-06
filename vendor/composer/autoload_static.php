<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitab2fe09babaa4a8af0f439f614c56dbc
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
        ),
        'D' => 
        array (
            'DvK\\Vat\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'DvK\\Vat\\' => 
        array (
            0 => __DIR__ . '/..' . '/dannyvankooten/vat.php/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitab2fe09babaa4a8af0f439f614c56dbc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitab2fe09babaa4a8af0f439f614c56dbc::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
