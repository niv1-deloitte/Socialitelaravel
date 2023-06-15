<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    
     // Show Register/Create Form
     public function create() {
        return view('users.register');
    }

     // Create New User
     public function store(Request $request) {
        $formFields = $request->validate([
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => 'required|confirmed|min:6'
        ]);

        // Hash Password
        $formFields['password'] = bcrypt($formFields['password']);

        // Create User
        $user = User::create($formFields);
        // Login
        auth()->login($user);

        return redirect('/')->with('message', 'User created and logged in');
     }
         // Logout User
     public function logout(Request $request) {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'You have been logged out!');

    }

     // Show Login Form
     public function login() {
        return view('users.login');
    }

    // Authenticate User
    public function authenticate(Request $request) {
        $formFields = $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required'
        ]);

        if(auth()->attempt($formFields)) {
            $request->session()->regenerate();

            return redirect('/')->with('message', 'You are now logged in!');
        }

        return back()->withErrors(['email' => 'Invalid Credentials'])->onlyInput('email');
    }

    public function redirect()
    {
        return Socialite::driver('google')->redirect();

    }
    public function callbackGoogle() {
        try {
            $google_user = Socialite::driver('google')->user();
            $user= User::where('google_id',$google_user->getId())->first();

            if(!$user) {
                $new_user=User::create([
                'name' => $google_user->getName(),
                'email' => $google_user->getEmail(),
                'google_id' => $google_user->getId()

                ]);

                 Auth::login($new_user);
                // auth()->login($new_user);

                return redirect()->intended('/');
                
            }
            else{
                Auth::login($user);
                return redirect()->intended('/');
            }
        }

        catch(\Throwable $th) {
            dd('error'.$th->getMessage());
        }
    }
}


    


