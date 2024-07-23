<?php

namespace fixtures\mymodule;

class mymodule extends \Module
{
    public function __construct()
    {
        $this->name = 'mymodule';
        $this->version = '1.0.0';
    }
}
