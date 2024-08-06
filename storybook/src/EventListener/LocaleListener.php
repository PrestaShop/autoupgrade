<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleListener
{
    /**
     * @param RequestEvent $event
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (isset($event->getRequest()->getPayload()->all('args')['_locale'])) {
            $request->setLocale(
                $request->getPayload()->all('args')['_locale']
            );
        }
    }
}
