<?php

// File generated from our OpenAPI spec

namespace StellarWP\Learndash\Stripe\Issuing;

/**
 * A Personalization Design is a logical grouping of a Physical Bundle, card logo, and carrier text that represents a product line.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|string|\StellarWP\Learndash\Stripe\File $card_logo The file for the card logo to use with physical bundles that support card logos. Must have a <code>purpose</code> value of <code>issuing_logo</code>.
 * @property null|\StellarWP\Learndash\Stripe\StripeObject $carrier_text Hash containing carrier text, for use with physical bundles that support carrier text.
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|string $lookup_key A lookup key used to retrieve personalization designs dynamically from a static string. This may be up to 200 characters.
 * @property \StellarWP\Learndash\Stripe\StripeObject $metadata Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
 * @property null|string $name Friendly display name.
 * @property string|\StellarWP\Learndash\Stripe\Issuing\PhysicalBundle $physical_bundle The physical bundle object belonging to this personalization design.
 * @property \StellarWP\Learndash\Stripe\StripeObject $preferences
 * @property \StellarWP\Learndash\Stripe\StripeObject $rejection_reasons
 * @property string $status Whether this personalization design can be used to create cards.
 */
class PersonalizationDesign extends \StellarWP\Learndash\Stripe\ApiResource
{
    const OBJECT_NAME = 'issuing.personalization_design';

    use \StellarWP\Learndash\Stripe\ApiOperations\All;
    use \StellarWP\Learndash\Stripe\ApiOperations\Create;
    use \StellarWP\Learndash\Stripe\ApiOperations\Retrieve;
    use \StellarWP\Learndash\Stripe\ApiOperations\Update;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVIEW = 'review';
}
