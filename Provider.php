<?php

namespace SocialiteProviders\TikTok;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use JsonException;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'TIKTOK';

    /**
     * The base TikTok Business URL.
     */
    protected string $url = 'https://business-api.tiktok.com/open_api/';

    /**
     * The TikTok API version for the request.
     */
    protected string $version = 'v1.3';

    /**
     * The user fields being requested.
     */
    protected array $fields = ['username', 'display_name', 'profile_image'];

    /**
     * {@inheritdoc}
     */
    protected $scopes = [
        'user.info.basic',
    ];

    /**
     * @var User
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return 'https://open-api.tiktok.com/platform/oauth/connect?' . http_build_query([
                'client_key' => $this->clientId,
                'state' => $state,
                'response_type' => 'code',
                'scope' => $this->formatScopes($this->getScopes(), $this->scopeSeparator),
                'redirect_uri' => $this->redirectUrl,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException();
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        $token = Arr::get($response, 'access_token');

        $this->user = $this->mapUserToObject($this->getUserByToken([
            'accessToken' => $token,
            'creatorId' => Arr::get($response, 'creator_id'),
        ]));

        return $this->user->setToken($token)
            ->setExpiresIn(Arr::get($response, 'expires'))
            ->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setApprovedScopes(explode($this->scopeSeparator, Arr::get($response, 'scope', '')));
    }

    /**
     * {@inheritdoc}
     *
     * @see https://ads.tiktok.com/marketing_api/docs?id=1738084387220481
     */
    public function getTokenUrl()
    {
        return $this->url . $this->version . '/oauth2/creator_token/';
    }

    /**
     * {@inheritdoc}
     *
     * @see https://ads.tiktok.com/marketing_api/docs?id=1738084387220481
     */
    protected function getTokenFields($code)
    {
        return [
            'business' => 'tt_user',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'auth_code' => $code,
        ];
    }

    /**
     * Return Business Account (User) by token
     *
     * @param array $data
     *
     * @throws JsonException
     * @throws GuzzleException
     */
    protected function getUserByToken($data)
    {
        $response = $this->getHttpClient()->get($this->url . $this->version . '/business/get/', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $data['accessToken']],
            RequestOptions::QUERY => [
                'business_id' => $data['creatorId'],
                'fields' => '["' . implode('","', $this->fields) . '"]',
            ],
        ]);

        return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject($user)
    {
        $user = $user['data'];

        return (new User())->setRaw($user)->map([
            'id' => $user['username'],
            'nickname' => $user['username'],
            'name' => $user['display_name'],
            'email' => null,
            'avatar' => $user['profile_image'],
        ]);
    }

    /**
     * Set the user fields to request from TikTok.
     */
    public function fields(array $fields): Provider
    {
        $this->fields = $fields;

        return $this;
    }
}
