<?php

namespace Sam\ApiPlatform\Messenger\Annotation;

use ApiPlatform\Core\Annotation\AttributesHydratorTrait;
use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes(
 *     @Attribute("path", type="string"),
 *     @Attribute("type", type="string")
 * )
 */
class ApiMessage
{
    use AttributesHydratorTrait;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $type;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $values = [])
    {
        $this->hydrateAttributes($values);
    }
}
