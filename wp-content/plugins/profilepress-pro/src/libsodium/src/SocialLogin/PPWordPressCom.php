<?php

namespace ProfilePress\Libsodium\SocialLogin;

use Hybridauth\Provider\WordPress;

class PPWordPressCom extends WordPress
{
    //specifically requires state to work
    protected $supportRequestState = true;

    protected $scope = 'auth';
}