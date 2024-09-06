<?php

namespace PrestaShop\Module\AutoUpgrade\Router;

use PrestaShop\Module\AutoUpgrade\Controller\WelcomeController;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class Router
{
    protected $upgradeContainer;

    public function __construct(UpgradeContainer $upgradeContainer)
    {
        $this->upgradeContainer = $upgradeContainer;
    }

    const PAGES = [
        'HOME' => 'home',
        'UPGRADE' => 'upgrade'
    ];
    const STEPS = [
        'NO_STEP' => 'no_step',
        'STEP_1' => 'step_1',
        'STEP_2' => 'step_2'
    ];

    const COMPONENTS = [
        'checklist' => [
            'twig' => 'blabla.htlm.twig',
            'params' => [
                'blabla',
                'titi',
                'toto'
            ]
        ]
    ];

    // url https://blabla.com?page=home&step=step_1

    const paramPage = 'home';
    const paramStep = 'step_1';

    const ROUTES = [
        'welcome' => [
            'controller' => WelcomeController::class,
            'method' => 'index',
            'params' => [],
        ],

//        self::PAGES['HOME'] => [
//            params => [],
//            self::STEPS['NO_STEP'] => [
//                components => []
//            ]
//        ],
//        self::PAGES['UPGRADE'] => [
//
//            self::STEPS['STEP_1'] => [
//                param => [],
//                components => [
//                    self::COMPONENTS['CHECKLIST']
//                ]
//            ],
//            self::STEPS['STEP_2'] => [
//                only_ajax => true,
//                components => []
//            ]
//        ]
    ];

    public function handle(string $route): string
    {
        // TODO: Assert mandatory params are provided
        // $this->validateParams();

        if (empty(self::ROUTES[$route])) {
            throw new \InvalidArgumentException('Oh no! The route does not exist');
        }
        $route = self::ROUTES[$route];

        return (new $route['controller']($this->upgradeContainer))->index();
        //return (new $route['controller']())->($route['method']);
    }
}
