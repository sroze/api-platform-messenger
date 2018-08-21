<?php

namespace Sam\ApiPlatform\Messenger\Factory\Metadata;

use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyNameCollection;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Exception\RuntimeException;

class AllowResourceWithoutProperty implements PropertyNameCollectionFactoryInterface
{
    private $decoratedFactory;
    private $metadataFactory;

    public function __construct(PropertyNameCollectionFactoryInterface $decoratedFactory, ResourceMetadataFactoryInterface $metadataFactory)
    {
        $this->decoratedFactory = $decoratedFactory;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass, array $options = []): PropertyNameCollection
    {
        try {
            return $this->decoratedFactory->create($resourceClass, $options);
        } catch (RuntimeException $e) {
            if ($this->metadataFactory->create($resourceClass)->getAttribute('_api_platform_messenger', false)) {
                return new PropertyNameCollection();
            }

            throw $e;
        }
    }
}
