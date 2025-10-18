<?php

namespace App\Auth\Socialite;

use App\Services\SocialAuth\SocialAuthStateManager;
use Illuminate\Http\RedirectResponse;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use SocialiteProviders\Twitter\Provider as BaseTwitterProvider;

class TwitterProvider extends BaseTwitterProvider
{
    protected ?SocialAuthStateManager $stateManager = null;
    protected ?string $stateReference = null;

    public function usingStateManager(SocialAuthStateManager $manager): static
    {
        $this->stateManager = $manager;

        return $this;
    }

    public function forState(?string $state): static
    {
        $this->stateReference = $state ?: null;

        return $this;
    }

    public function redirect()
    {
        $temporaryCredentials = $this->server->getTemporaryCredentials();

        if ($this->stateManager && $this->stateReference) {
            $this->stateManager->storeOauth1Credentials($this->stateReference, $temporaryCredentials);
        }

        return new RedirectResponse($this->server->getAuthorizationUrl($temporaryCredentials));
    }

    protected function getToken()
    {
        if ($this->stateManager && $this->stateReference) {
            $credentials = $this->stateManager->pullOauth1Credentials($this->stateReference);

            if ($credentials instanceof TemporaryCredentials) {
                return $this->server->getTokenCredentials(
                    $credentials,
                    $this->request->get('oauth_token'),
                    $this->request->get('oauth_verifier')
                );
            }
        }

        return parent::getToken();
    }
}
