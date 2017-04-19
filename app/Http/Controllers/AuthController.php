<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SpotifyWebAPI\Session as SpotifySession;
use SpotifyWebAPI\SpotifyWebAPI;

class AuthController extends Controller
{
    protected $session;

    public function __construct()
    {
        $this->session = new SpotifySession(
            config('services.spotify.clientId'),
            config('services.spotify.clientSecret'),
            config('app.url') . '/callback'
        );
    }

    public function login()
    {
        $options = [
            'scope' => [
                'user-read-currently-playing',
                'user-read-email',
                'user-read-private',
            ],
        ];

        return redirect()->to($this->session->getAuthorizeUrl($options));
    }

    public function callback(Request $request)
    {
        $this->session->requestAccessToken($request->get('code'));
        $accessToken = $this->session->getAccessToken();

        $api = new SpotifyWebAPI;
        $api->setAccessToken($accessToken);

        $me = $api->me();

        $user = User::where('spotify_id', $me->id)->first();

        if (! $user) {
            $user = new User;
        }

        $user->name                 = $me->display_name;
        $user->email                = $me->email;
        $user->access_token         = $accessToken;
        $user->refresh_token        = $this->session->getRefreshToken();
        $user->country              = $me->country;
        $user->spotify_id           = $me->id;
        $user->spotify_subscription = $me->product;
        $user->save();

        Auth::login($user, true);

        return redirect()->to('/');
    }
}
