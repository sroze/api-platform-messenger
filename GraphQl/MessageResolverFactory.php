<?php

namespace Sam\ApiPlatform\Messenger\GraphQl;

use ApiPlatform\Core\GraphQl\Resolver\Factory\ResolverFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use ApiPlatform\Core\GraphQl\Serializer\ItemNormalizer;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MessageResolverFactory implements ResolverFactoryInterface
{
    private $decoratedResolver;
    private $resourceMetadataFactory;
    private $messageBus;
    private $denormalizer;
    private $normalizer;

    public function __construct(
        ResolverFactoryInterface $decoratedResolver,
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        DenormalizerInterface $denormalizer,
        NormalizerInterface $normalizer,
        MessageBusInterface $messageBus
    )
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->denormalizer = $denormalizer;
        $this->normalizer = $normalizer;
        $this->messageBus = $messageBus;
    }

    public function __invoke(string $resourceClass = null, string $rootClass = null, string $operationName = null, ResourceMetadata $metadata = null): callable
    {
        if (null === $messageClass = $metadata->getAttribute('_api_platform_messenger')) {
            return $this->decoratedResolver->__invoke($resourceClass, $rootClass, $operationName, $metadata);
        }

        return function ($root, $args, $context, ResolveInfo $info) use ($messageClass, $operationName, $metadata) {
            $message = $this->denormalizer->denormalize(
                $args['input'] ?? [],
                $messageClass
            );

            $normalizationContext = $metadata->getGraphqlAttribute($operationName ?? '', 'normalization_context', [], true);
            $normalizationContext['attributes'] = $info->getFieldSelection(PHP_INT_MAX);

            return $this->normalizer->normalize(
                $this->messageBus->dispatch($message),
                ItemNormalizer::FORMAT,
                $normalizationContext
            );
        };
    }
}
