<?php

namespace StellarWP\Learndash\Razorpay\Api;

use Countable;

class Collection extends Entity implements Countable
{
    public function count():int
    {
        $count = 0;

        if (isset($this->attributes['count']))
        {
            return $this->attributes['count'];
        }

        return $count;
    }
}
