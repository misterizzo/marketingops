<?php

namespace ProfilePressVendor\Carbon\Doctrine;

use ProfilePressVendor\Carbon\Carbon;
use ProfilePressVendor\Doctrine\DBAL\Types\VarDateTimeType;
class DateTimeType extends VarDateTimeType implements CarbonDoctrineType
{
    /** @use CarbonTypeConverter<Carbon> */
    use CarbonTypeConverter;
}
