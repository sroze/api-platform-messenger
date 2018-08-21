<?php

namespace Sam\ApiPlatform\Messenger\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class Dispatch
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(Request $request, $data)
    {
        if ('query' === $request->attributes->get('_api_platform_messenger_type')) {
            $resourceClass = $request->attributes->get('_api_resource_class');

            $data = new $resourceClass();
        }

        $result = $this->messageBus->dispatch($data);
        if (empty($result)) {
            return new Response('', 204);
        }

        return $result;
    }
}
