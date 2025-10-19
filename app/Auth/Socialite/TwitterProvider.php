<?php

namespace App\Auth\Socialite;

use App\Services\SocialAuth\SocialAuthStateManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
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

        Log::debug('TwitterProvider generated temporary credentials', [
            'state_hash' => $this->summarizeState($this->stateReference),
            'identifier_tail' => $this->identifierTail($temporaryCredentials->getIdentifier()),
        ]);

        if ($this->stateManager && $this->stateReference) {
            $this->stateManager->storeOauth1Credentials($this->stateReference, $temporaryCredentials);
        }

        return new RedirectResponse($this->server->getAuthorizationUrl($temporaryCredentials));
    }

    protected function getToken()
    {
        if ($this->stateManager && $this->stateReference) {
            Log::debug('TwitterProvider attempting token exchange via state manager', [
                'state_hash' => $this->summarizeState($this->stateReference),
                'has_oauth_token' => (bool) $this->request->get('oauth_token'),
                'has_oauth_verifier' => (bool) $this->request->get('oauth_verifier'),
            ]);

            $credentials = $this->stateManager->pullOauth1Credentials($this->stateReference);

            if ($credentials instanceof TemporaryCredentials) {
                Log::debug('TwitterProvider retrieved cached OAuth1 credentials', [
                    'state_hash' => $this->summarizeState($this->stateReference),
                    'identifier_tail' => $this->identifierTail($credentials->getIdentifier()),
                ]);

                return $this->server->getTokenCredentials(
                    $credentials,
                    $this->request->get('oauth_token'),
                    $this->request->get('oauth_verifier')
                );
            }

            Log::warning('TwitterProvider missing cached OAuth1 credentials, falling back to session', [
                'state_hash' => $this->summarizeState($this->stateReference),
            ]);
        }

        Log::debug('TwitterProvider falling back to base token retrieval', [
            'has_state_manager' => (bool) $this->stateManager,
            'state_hash' => $this->summarizeState($this->stateReference),
        ]);

        return parent::getToken();
    }

    private function summarizeState(?string $state): ?string
    {
        if (! $state) {
            return null;
        }

        return substr(hash('sha256', $state), 0, 12);
    }

    private function identifierTail(?string $identifier): ?string
    {
        if (! $identifier) {
            return null;
        }

        return substr($identifier, -6);
    }
}
