<?php
/**
 * @license MIT
 *
 * Modified by learndash on 20-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\DeepCopy\Matcher;

/**
 * @final
 */
class PropertyMatcher implements Matcher
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $property;

    /**
     * @param string $class    Class name
     * @param string $property Property name
     */
    public function __construct($class, $property)
    {
        $this->class = $class;
        $this->property = $property;
    }

    /**
     * Matches a specific property of a specific class.
     *
     * {@inheritdoc}
     */
    public function matches($object, $property)
    {
        return ($object instanceof $this->class) && $property == $this->property;
    }
}
