<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\SocialAccount;
use App\User;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
    	// driver:: memilih social media untuk login 
    	// $provider sosial media yang digunakan
    	return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {

    	try {
    		// data user ada
    		$user = Socialite::driver($provider)->user();
    	} catch (Exception $e) {
    		// data user tidak ada
    		return redirect('/login');
    	}

    	// data user didapatkan dan digunakan untuk loghin
    	$authUser = $this->findOrCreateUser($user, $provider);

    	// proses login
    	Auth::login($authUser, true);
    	// berhasil login 
    	return redirect('/home');
    }

    public function findOrCreateUser($socialUser, $provider)
    {
    	// mencari data dari provider_id
    	$socialAccount = SocialAccount::where('provider_id', $socialUser->getId())
    							->where('provider_name', $provider)
    							->first();

    	// data user ada
    	if ($socialAccount) {
    		return $socialAccount->user;
    	} else {
    		// mencari data user
    		$user = User::where('email', $socialUser->getEmail())->first();
    		// data user tidak ada dan membuat user baru
    		if (! $user) {
    			$user =  User::create([
    				'name' => $socialUser->getName(),
    				'email' => $socialUser->getEmail()
    			]);
    		}

    		// data user untuk login ada atau baru dibuat
    		$user->socialAccounts()->create([
    			'provider_id' => $socialUser->getId(),
    			'provider_name' => $provider
    		]);

    		// memberi nilai balik untuk login
    		return $user;
    	}						
    }
}

