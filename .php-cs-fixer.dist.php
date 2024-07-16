<?php

$config = new PrestaShop\CodingStandards\CsFixer\Config();

$config
    ->setUsingCache(true)
    ->getFinder()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('node_modules');

return $config;
