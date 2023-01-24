<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0d79e55a2501816e7218114a159fd3e9
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPTRT\\AdminNotices\\' => 19,
        ),
        'B' => 
        array (
            'Barn2\\VAT_Lib\\' => 14,
            'Barn2\\Plugin\\EDD_VAT\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPTRT\\AdminNotices\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib/vendor/admin-notices/src',
        ),
        'Barn2\\VAT_Lib\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
        'Barn2\\Plugin\\EDD_VAT\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Api' => __DIR__ . '/../..' . '/dependencies/src/Api.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Interfaces\\Bootable' => __DIR__ . '/../..' . '/dependencies/src/Interfaces/Bootable.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Interfaces\\Deferrable' => __DIR__ . '/../..' . '/dependencies/src/Interfaces/Deferrable.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Interfaces\\Pluggable' => __DIR__ . '/../..' . '/dependencies/src/Interfaces/Pluggable.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Interfaces\\Restartable' => __DIR__ . '/../..' . '/dependencies/src/Interfaces/Restartable.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Setup_Wizard' => __DIR__ . '/../..' . '/dependencies/src/Setup_Wizard.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Starter' => __DIR__ . '/../..' . '/dependencies/src/Starter.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Step' => __DIR__ . '/../..' . '/dependencies/src/Step.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Steps\\Cross_Selling' => __DIR__ . '/../..' . '/dependencies/src/Steps/Cross_Selling.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Steps\\Ready' => __DIR__ . '/../..' . '/dependencies/src/Steps/Ready.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Steps\\Welcome' => __DIR__ . '/../..' . '/dependencies/src/Steps/Welcome.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Steps\\Welcome_Free' => __DIR__ . '/../..' . '/dependencies/src/Steps/Welcome_Free.php',
        'Barn2\\Plugin\\EDD_VAT\\Dependencies\\Barn2\\Setup_Wizard\\Util' => __DIR__ . '/../..' . '/dependencies/src/Util.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0d79e55a2501816e7218114a159fd3e9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0d79e55a2501816e7218114a159fd3e9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0d79e55a2501816e7218114a159fd3e9::$classMap;

        }, null, ClassLoader::class);
    }
}
