<?php

namespace ProfilePress\Libsodium\SocialLogin;

use Hybridauth\Provider\MicrosoftGraph;

class PPMicrosoft extends MicrosoftGraph
{
    protected $supportRequestState = false;
}