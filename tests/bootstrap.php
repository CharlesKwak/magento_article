<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap for ThirdParty_BlogArticle smoke tests.
 * Loads Magento stubs first so registration.php and module classes autoload
 * without a full Magento application.
 */

require __DIR__ . '/stubs/MagentoFrameworkStubs.php';
require dirname(__DIR__) . '/vendor/autoload.php';
