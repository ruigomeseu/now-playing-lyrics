<?php

namespace App\Http\Controllers;

use Genius\Genius;
use Illuminate\Support\Facades\Auth;
use SpotifyWebAPI\SpotifyWebAPI;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user) {
            $api = new SpotifyWebAPI;
            $api->setAccessToken($user->access_token);

            $currentTrack = $api->getMyCurrentTrack();
            if (! $currentTrack->is_playing) {
                return view('no-results');
            }

            $song   = $currentTrack->item->name;
            $artist = $currentTrack->item->artists[0]->name;

            $genius = new Genius(config('services.genius.accessToken'));
            $result = $genius->search->get("{$song} {$artist}");
            $url    = $result->response->hits[0]->result->url;

            return redirect()->to($url);
        } else {
            return view('welcome');
        }
    }
}
