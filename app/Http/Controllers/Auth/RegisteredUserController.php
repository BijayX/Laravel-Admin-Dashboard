<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse

    {
         
        $request->validate([
            'name' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Check if the name is empty
                    if (empty($value)) {
                        $fail('The '.$attribute.' is required.');
                    } else {
                        // Trim and sanitize the name
                        $cleanedName = htmlspecialchars(trim($value));
            
                        // Check if the cleaned name matches the desired pattern
                        if (!preg_match("/^[a-zA-Z\s]+$/", $cleanedName)) {
                            $fail('The '.$attribute.' should contain characters and whitespace only.');
                        }
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:'.User::class,
                function ($attribute, $value, $fail) {
                    // Check for a valid email format
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('The '.$attribute.' must be a valid email address.');
                    }
            
                    // Check for only one occurrence of ".com"
                    if (substr_count($value, '.com') !== 1) {
                        $fail('The '.$attribute.' must contain exactly one ".com".');
                    }
                },
            ],         

            'role' => ['required', 'in:user,admin'],


            'password' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Check if the password is empty
                    if (empty($value)) {
                        $fail('The '.$attribute.' is required.');
                    } else {
                        // Check if the password matches the desired pattern
                        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/', $value)) {
                            $fail('The '.$attribute.' should contain at least one uppercase letter, one lowercase letter, one digit, one symbol, and be at least 8 characters long.');
                        }
                    }
                },
                'confirmed', 
            ],
           
        ]); // Add the missing closing bracket here
      

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        return redirect()->route('login')->with('success', 'Registration successful! Please log in.');
    }
}
