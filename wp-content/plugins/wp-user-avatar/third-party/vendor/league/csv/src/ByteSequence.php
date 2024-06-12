<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ProfilePressVendor\League\Csv;

/**
 * Defines constants for common BOM sequences.
 */
interface ByteSequence
{
    const BOM_UTF8 = "﻿";
    const BOM_UTF16_BE = "\xfe\xff";
    const BOM_UTF16_LE = "\xff\xfe";
    const BOM_UTF32_BE = "\x00\x00\xfe\xff";
    const BOM_UTF32_LE = "\xff\xfe\x00\x00";
}
