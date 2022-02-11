<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable // implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['roles'];


    /** Used to automatically add the "roles" field/attribute to serialized
     * responses.
     *
     * For more info see:
     * https://laravel.com/docs/8.x/eloquent-serialization#appending-values-to-json
     */
    public function getRolesAttribute()
    {
        return $this->roles()->get(['id']);
    }

    /** Get the posts that this user has made. */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    /** Get the comments that this user has made. */
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    /** Get the roles that this user has. */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole($roleId)
    {
        // This was inspired by code from:
        // https://stackoverflow.com/questions/24555697/check-if-belongstomany-relation-exists-laravel
        return $this->roles()->where('id', $roleId)->exists();
    }
    public function hasNamedRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }
    /** Check if this user has the administrator role. */
    public function isAdministrator()
    {
        return $this->hasNamedRole('Administrator');
    }

    public function scrambleText($enhancedText, ?User $userThatWillBeShownText)
    {
        if ($this->id === $userThatWillBeShownText?->id) {
            return $enhancedText;
        }

        // TODO: store this inside the database.
        $algorithm = rand(0, 5);
        if ($algorithm === 0) {
            // randomBetweenStartAndEndOfEachWord:
            $lines = preg_split('/\r\n|\r|\n/', $enhancedText);
            $lines = array_map(function($line) {
                // Separate by words:
                $words = explode(' ', $line);
                $words = array_map(function($word) {
                    if (strlen($word) < 2)
                        return $word;
                    else
                        return $word[0] . str_shuffle(substr($word, 1, -1)) . $word[strlen($word) - 1];
                }, $words);
                $line = implode(' ', $words);
                return $line;
            }, $lines);
            $enhancedText = implode("\n", $lines);
            $enhancedText = preg_replace("/^-(.*)-$/", Str::random(), $enhancedText, -1);
            return $enhancedText;
        } else if ($algorithm === 1) {
            // extraConsonantForDoubleConsonants:
            $enhancedText = preg_replace('"är"', 'ä', $enhancedText);
            $enhancedText = preg_replace('"Är"', 'Ä', $enhancedText);
            $enhancedText = preg_replace('/e/i', 'ä', $enhancedText);
            return $enhancedText;
        } else if ($algorithm === 2) {
            // The 'Uwuifier' Github repository: https://github.com/Schotsl/Uwuifier-node
            // gives some credit to a web extension: https://addons.mozilla.org/sv-SE/firefox/addon/owofox/
            // that inspired this code:
            $faces = [" (・`ω´・) ", " ;;w;; ", " owo ", " UwU ", " >w< ", " ^w^ "];
            $enhancedText = preg_replace('/(?:r|l)/', "w", $enhancedText);
            $enhancedText = preg_replace('/(?:R|L)/', "W", $enhancedText);
            $enhancedText = preg_replace('/n([aeiou])/', 'ny$1', $enhancedText);
            $enhancedText = preg_replace('/N([aeiou])/', 'Ny$1', $enhancedText);
            $enhancedText = preg_replace('/N([AEIOU])/', 'Ny$1', $enhancedText);
            $enhancedText = preg_replace('/ove/', "uv", $enhancedText);
            $enhancedText = preg_replace_callback('/\!+/', function () use ($faces) {
                // We want this (but the value should not change between reloads):
                $faceIndex = rand(0, 6);

                //  This is kinda random, but it won't change between reloads.
                // Improve this by using a real random generator and seed it
                // with our kinda random value for better randomness.
                //
                // We rely on rand everywhere anyways, so don't care about this:
                /*
                $somewhatRandomSeed = $this->id + $textId + $matchCount;
                $faceIndex = $somewhatRandomSeed % 6;
                $matchCount += 1;
                */

                return $faces[$faceIndex];
            }, $enhancedText);
            return $enhancedText;
        } else if ($algorithm === 3) {
            // allCaps
            $enhancedText = preg_replace_callback('/"[a-z]/', function ($matches) {
                return strtoLower($matches[0]);
            }, strtoUpper($enhancedText));
            return $enhancedText;
        } else if ($algorithm === 4) {
            // sarcasmOverload
            $enhancedText = preg_replace_callback('!([a-zA-Z]\d*)([a-zA-Z])!', function ($matches) {
                return strtolower($matches[1]) . strtoupper($matches[2]);
            }, $enhancedText);
            return $enhancedText;
        } else if($algorithm === 5) {
            // oneLiner
            return $enhancedText = str_replace([".", "!", "?", "\n", "\t", "\r"], '', substr(strtolower($enhancedText), 1));
        } else if($algorithm === 6) {
            // theElegantNetOfThePeople
            // replace the following patterns with a random result for each occurrence
            $youIsU = ['u', 'U'];
            $exclaiming = [' lol', ' lmao', ' rofl', ' literally', ' XD'];
            $oh = ['o', 'O'];
            $r = ['', '/"[a-z]/'];
            $f = ['', ' F ', '/"[a-z]/'];
            $e = ['', ' E ', '/"[a-z]/'];
            $enhancedText = preg_replace_callback('~/you/i~', function () use ($youIsU) {
                $youIsUIndex = rand(0, 1);
                return $youIsU[$youIsUIndex];
            }, $enhancedText);
            $enhancedText = preg_replace('/and/i', "&", $enhancedText);
            $enhancedText = preg_replace('/one/i', '1', $enhancedText);
            $enhancedText = preg_replace('/free/i', '3', $enhancedText);
            $enhancedText = preg_replace(['/to/i', '/too/i'], '2', $enhancedText);
            $enhancedText = preg_replace('/for/i', '4', $enhancedText);
            $enhancedText = preg_replace('/ate/i', '8', $enhancedText);
            $enhancedText = preg_replace('/a/i', 'u', $enhancedText);
            $enhancedText = preg_replace('/that/i', 'dat', $enhancedText);
            $enhancedText = preg_replace('/this/i', 'dis', $enhancedText);
            $enhancedText = preg_replace('/the/i', 'theh', $enhancedText);
            $enhancedText = preg_replace('/why/i', 'y', $enhancedText);
            $enhancedText = preg_replace('/w/i', 'v', $enhancedText);
            $enhancedText = preg_replace('/my/i', 'our', $enhancedText);
            $enhancedText = preg_replace('/community/i', 'comrades', $enhancedText);
            $enhancedText = preg_replace_callback('~!~', function () use ($exclaiming) {
                $exclaimingIndex = rand(0, 4);
                return $exclaiming[$exclaimingIndex];
            }, $enhancedText);
            $enhancedText = preg_replace_callback('~/oh/i~', function () use ($oh) {
                $ohIndex = rand(0, 1);
                return $oh[$ohIndex];
            }, $enhancedText);
            $enhancedText = preg_replace('/really/i', 'rly', $enhancedText);
            $enhancedText = preg_replace_callback('~/r/i~', function () use ($r) {
                $rIndex = rand(0, 1);
                return $r[$rIndex];
            }, $enhancedText);
            $enhancedText = preg_replace_callback('~/f/i~', function () use ($f) {
                $fIndex = rand(0, 2);
                return $f[$fIndex];
            }, $enhancedText);
            $enhancedText = preg_replace_callback('~/e/i~', function () use ($e) {
                $eIndex = rand(0, 2);
                return $e[$eIndex];
            }, $enhancedText);
            return $enhancedText;
        } else if($algorithm === 7) {
            // whySayManyWordWhenFewWordDoTrick
        } else if($algorithm === 8) {
            // botchedKeyToKeyboardLayout
        } else {
            throw new \Exception("Invalid text processing algorithm: $algorithm");
        }
    }
}
