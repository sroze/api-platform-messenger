<?php

namespace Sam\ApiPlatform\Messenger\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Doctrine\Common\Annotations\Reader;
use Sam\ApiPlatform\Messenger\Annotation\ApiMessage;

final class AnnotationResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private $reader;
    private $decorated;

    public function __construct(Reader $reader, ResourceMetadataFactoryInterface $decorated = null)
    {
        $this->reader = $reader;
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass): ResourceMetadata
    {
        $parentResourceMetadata = null;
        if ($this->decorated) {
            try {
                $parentResourceMetadata = $this->decorated->create($resourceClass);
            } catch (ResourceClassNotFoundException $resourceNotFoundException) {
                // Ignore not found exception from decorated factories
            }

        }

        if ($parentResourceMetadata) {
            return $parentResourceMetadata;
        }

        try {
            $reflectionClass = new \ReflectionClass($resourceClass);
        } catch (\ReflectionException $reflectionException) {
            return $this->handleNotFound($parentResourceMetadata, $resourceClass);
        }

        $resourceAnnotation = $this->reader->getClassAnnotation($reflectionClass, ApiMessage::class);
        if (null === $resourceAnnotation) {
            return $this->handleNotFound($parentResourceMetadata, $resourceClass);
        }

        return $this->createMetadata($resourceAnnotation);
    }

    /**
     * Returns the metadata from the decorated factory if available or throws an exception.
     *
     * @throws ResourceClassNotFoundException
     */
    private function handleNotFound(ResourceMetadata $parentPropertyMetadata = null, string $resourceClass): ResourceMetadata
    {
        if (null !== $parentPropertyMetadata) {
            return $parentPropertyMetadata;
        }

        throw new ResourceClassNotFoundException(sprintf('Resource "%s" not found.', $resourceClass));
    }

    private function createMetadata(ApiMessage $annotation): ResourceMetadata
    {
        $method = $annotation->type === 'command' ? 'post' : 'get';
        $collectionOperations = [
            $method => ['method' => $method, 'controller' => 'api_platform_messenger.action.dispatch', 'defaults' => ['_api_platform_messenger_type' => $annotation->type]]
        ];

        return new ResourceMetadata(
            str_replace('/', '', $annotation->path),
            null,
            $annotation->path,
            [],
            $collectionOperations,
            array_merge(['_api_platform_messenger' => true], $annotation->attributes),
            [],
            null
        );
    }
}
