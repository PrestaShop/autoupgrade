<?php

/**
 * This module usually runs without relying of PrestaShop classes.
 * During the initialization of PHPStan in a module context, PrestaShop Autoloader is always called
 * to make sure module calls are known.
 *
 * To avoid signature mismatch reported by PHPStan, we force the loading of some of our classes in memory.
 */

use Symfony\Component\Console\Output\Output;

class_exists(Output::class);

// We load Twig classes to avoid conflicts with old versions of PrestaShop providing old versions of the library.
// Avoid Reflection error: Circular reference to class "Twig_Environment"
require_once __DIR__ . '/../../vendor/twig/twig/src/Environment.php';

// Avoid Reflection error: Circular reference to class "Twig\Error\Error"
require_once __DIR__ . '/../../vendor/twig/twig/src/Error/Error.php';

// Avoid Reflection error: Circular reference to class "Twig_ExtensionInterface"
require_once __DIR__ . '/../../vendor/twig/twig/src/Extension/ExtensionInterface.php';
require_once __DIR__ . '/../../vendor/twig/twig/src/Extension/AbstractExtension.php';

// Avoid Reflection error: Circular reference to class "Twig_LoaderInterface"
require_once __DIR__ . '/../../vendor/twig/twig/src/Loader/LoaderInterface.php';
require_once __DIR__ . '/../../vendor/twig/twig/src/Loader/ExistsLoaderInterface.php';
require_once __DIR__ . '/../../vendor/twig/twig/src/Loader/SourceContextLoaderInterface.php';
