<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

class AbstractPageController extends AbstractGlobalController
{
    protected function getPsVersion(): string
    {
        return $this->upgradeContainer->getProperty($this->upgradeContainer::PS_VERSION);
    }

    private function getPsVersionClass(): string
    {
        $psVersion = $this->getPsVersion();
        $psClass = '';

        if (version_compare($psVersion, '1.7.8.0', '<')) {
            $psClass = 'v1-7-3-0';
        } elseif (version_compare($psVersion, '9.0.0', '<')) {
            $psClass = 'v1-7-8-0';
        }

        return $psClass;
    }

    public function renderPage(string $page, array $params): string
    {
        return $this->twig->render(
            '@ModuleAutoUpgrade/layouts/layout.html.twig',
            array_merge(
                [
                    'page' => $page,
                    'ps_version' => $this->getPsVersionClass(),
                ],
                $params
            )
        );
    }
}
