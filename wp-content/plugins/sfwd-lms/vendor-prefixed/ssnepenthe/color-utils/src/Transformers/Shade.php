<?php

namespace StellarWP\Learndash\SSNepenthe\ColorUtils\Transformers;

use StellarWP\Learndash\SSNepenthe\ColorUtils\Colors\Rgb;
use StellarWP\Learndash\SSNepenthe\ColorUtils\Colors\Color;

/**
 * Class Shade
 */
class Shade implements TransformerInterface
{
    /**
     * @var Mix
     */
    protected $transformer;

    /**
     * @param int $weight
     */
    public function __construct(int $weight = 50)
    {
        $this->transformer = new Mix(new Color(new Rgb(0, 0, 0)), $weight);
    }

    /**
     * @param Color $color
     * @return Color
     */
    public function transform(Color $color) : Color
    {
        return $this->transformer->transform($color);
    }
}
