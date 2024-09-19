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
