<?php

declare(strict_types=1);

namespace App\Socialite;

use Laravel\Socialite\Two\GoogleProvider;

/**
 * Socialite provider dedicated to the Google Drive integration.
 *
 * Inherits the entire OAuth flow from the stock GoogleProvider but exists as a
 * separate class so we can register it under the `google-drive` driver name in
 * the service provider, keeping it independent from the YouTube (`google`)
 * and login (`google-auth`) drivers — each of those reads its own credentials
 * and redirect URI from `config/services.php`.
 */
class GoogleDriveProvider extends GoogleProvider
{
}
