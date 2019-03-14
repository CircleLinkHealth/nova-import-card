<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcac27fc9bd2a99fa15787634034377c4
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'CircleLinkHealth\\Raygun\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'CircleLinkHealth\\Raygun\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $prefixesPsr0 = array (
        'R' => 
        array (
            'Raygun4php' => 
            array (
                0 => __DIR__ . '/..' . '/mindscape/raygun4php/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitcac27fc9bd2a99fa15787634034377c4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitcac27fc9bd2a99fa15787634034377c4::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitcac27fc9bd2a99fa15787634034377c4::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
