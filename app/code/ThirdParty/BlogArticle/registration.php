<?php
declare(strict_types=1);

/**
 * Magento module registration.
 * Guarded so Composer autoload can load this file during standalone PHPUnit
 * smoke tests without a full Magento bootstrap. Inside Magento, the framework
 * class is always present and registration proceeds normally.
 */
if (class_exists(\Magento\Framework\Component\ComponentRegistrar::class)) {
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'ThirdParty_BlogArticle',
        __DIR__
    );
}
