<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use SebastianBergmann\Environment\Console;

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
        'content_scrambler_algorithm',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'content_scrambler_algorithm',
        'entry',
        'entry_id',
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

    /**
     * All of the relationships to be touched. (Sync "updated_at" timestamp)
     *
     * @var array
     */
    protected $touches = ['entry'];


    /**
     * Get the "entry" that owns this model.
     */
    public function entry()
    {
        return $this->hasOne(Entry::class);
    }


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

    /** The posts that this user has made. */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    /** The comments that this user has made. */
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }
    /** The actions that this user has done. Such as creating a post or "liking" something. */
    public function actions()
    {
        return $this->belongsToMany(Entry::class, 'user_actions', 'user_id', 'entry_id');
    }

    public function liked_entries()
    {
        // Info: https://laravel.com/docs/9.x/eloquent-relationships#defining-custom-intermediate-table-models
        return $this->belongsToMany(Entry::class, 'likes', 'user_id', 'likeable_id')->using(Like::class);
    }

    /** The likes or dislikes that this user has expressed. */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function like(Entry $entry)
    {
        return $this->createLike($entry, true);
    }
    public function dislike(Entry $entry)
    {
        return $this->createLike($entry, false);
    }
    /** Add a like or dislike from this user to a certain entry. */
    public function createLike(Entry $entry, bool $isLike)
    {
        return DB::transaction(function () use ($entry, $isLike) {
            $previousLike = $this->likeInfo($entry)->first();
            if ($previousLike !== null) {
                if ($previousLike->is_like === $isLike) {
                    // The correct opinion is already expressed (for example a
                    // like if we wanted to like):
                    return $previousLike;
                } else {
                    // Delete the incorrect opinion (for example a dislike if we
                    // want to like):
                    $previousLike->delete();
                }
            }

            $like = new Like(['is_like' => $isLike]);
            $like->user()->associate($this);
            $like->likeable()->associate($entry);
            $like->save();

            // Create entry for like:
            Entry::createForUser($this, $like);

            return $like;
        });
    }
    /**
     * Remove a like or disliked made by this user.
     *
     * @param \App\Models\Entry $entry The `Entry` which the like or disliked
     * should be removed from.
     * @param ?Callable $conditionalRemove This callback is provided the `Like`
     * that is about to remove and can return `false` if the like shouldn't
     * actually be removed.
     * @return bool `true` if a `Like` was removed.
     */
    public function removeLike(Entry $entry, ?callable $conditionalRemove = null)
    {
        // Callable type hint:
        // https://stackoverflow.com/questions/29730720/php-type-hinting-difference-between-closure-and-callable
        return DB::transaction(function () use ($entry, $conditionalRemove) {
            $like = $this->likeInfo($entry)->first();
            if ($like === null) return false;

            if ($conditionalRemove !== null && $conditionalRemove($like) === false) {
                // Canceled:
                return false;
            }

            $like->delete();

            return true;
        });
    }
    /** A query for like info for an entry. Chain with `->first()` to get the info
     * or `->exists()` to check for its existence. */
    public function likeInfo(Entry $likeable)
    {
        // See:
        // - https://stackoverflow.com/questions/24555697/check-if-belongstomany-relation-exists-laravel
        // - https://dev.to/bdelespierre/how-to-implement-a-simple-like-system-with-laravel-lfe
        return $this->likes()
            ->whereHas('likeable', fn ($q) => $q->whereId($likeable->id));
    }

    /** The roles that this user has. */
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
        return $this->hasNamedRole(Role::ADMIN);
    }
    /** Check if this user is disabled. */
    public function isDisabled()
    {
        return $this->hasNamedRole(Role::DISABLED);
    }

    /**
     * The max value for the content scrambling algorithm attribute.
     *
     * @var int
     */
    public const MAX_SCRAMBLE_ALGORITHM_VALUE = 8;

    public function scrambleText($enhancedText, ?User $userThatWillBeShownText = null)
    {
        if ($this->id === $userThatWillBeShownText?->id) {
            return $enhancedText;
        }

        $algorithm = $this->content_scrambler_algorithm;
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
            // $enhancedText = preg_replace("/^-(.*)-$/", Str::random(), $enhancedText, -1);
            return $enhancedText;
        } else if ($algorithm === 1) {
            // extraConsonantForDoubleConsonants:
            $enhancedText = str_replace("är", 'ä', $enhancedText);
            $enhancedText = str_replace("Är", 'Ä', $enhancedText);
            $enhancedText = str_replace('e', 'ä', $enhancedText);
            $enhancedText = str_replace('E', 'Ä', $enhancedText);
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
            return $enhancedText = str_replace([".", "!", "?", "\n", "\t", "\r"], '', ucfirst(strtolower($enhancedText)));
        } else if($algorithm === 6) {
            // theElegantNetOfThePeople
            // replace the following patterns with a random result for each occurrence
            $youIsU = ['u', 'U'];
            $exclaiming = [' lol', ' lmao', ' rofl', ' literally!', ' smh.', ' XD'];
            $o = ['o', '0'];
            // $r = ['', '/[a-zA-Z]/'];
            // $f = ['', ' F ', '/[a-zA-Z]/'];
            // $e = ['', ' E ', '/[a-zA-Z]/'];
            $enhancedText = preg_replace_callback('/you/i', function () use ($youIsU) {
                $youIsUIndex = rand(0, 1);
                return $youIsU[$youIsUIndex];
            }, $enhancedText);
            $enhancedText = str_ireplace('and', "&", $enhancedText);
            $enhancedText = str_ireplace(['be', 'bee'], 'b', $enhancedText);
            $enhancedText = str_ireplace(['okay', 'ok'], 'k', $enhancedText);
            $enhancedText = str_ireplace('one', '1', $enhancedText);
            $enhancedText = str_ireplace(['free', 'three', 'tree'], '3', $enhancedText);
            $enhancedText = str_ireplace(['to', 'too', 'two'], '2', $enhancedText);
            $enhancedText = str_ireplace('for', '4', $enhancedText);
            $enhancedText = str_ireplace(['ate', 'ight'], '8', $enhancedText);
            $enhancedText = str_ireplace('really', 'rly', $enhancedText);
            $enhancedText = str_ireplace('a', 'e', $enhancedText);
            $enhancedText = str_ireplace('that', 'dat', $enhancedText);
            $enhancedText = str_ireplace('this', 'dis', $enhancedText);
            $enhancedText = str_ireplace('they', 'dey', $enhancedText);
            $enhancedText = str_ireplace('those', 'dose', $enhancedText);
            $enhancedText = str_ireplace('the', 'da', $enhancedText);
            $enhancedText = str_ireplace('oh', 'o', $enhancedText);
            $enhancedText = str_ireplace('why', 'y', $enhancedText);
            $enhancedText = str_ireplace('though', 'tho(ugh)', $enhancedText);
            $enhancedText = str_ireplace(' I ', ' ಠ ', $enhancedText);
            $enhancedText = str_ireplace('eye', 'i', $enhancedText);
            $enhancedText = str_ireplace('see', 'c', $enhancedText);
            $enhancedText = str_ireplace(',', ', ತಎತ,', $enhancedText);
            $enhancedText = str_ireplace('w', 'vv', $enhancedText);
            $enhancedText = str_ireplace('en', 'n', $enhancedText);
            $enhancedText = str_ireplace('el', 'l', $enhancedText);
            $enhancedText = str_ireplace('my', 'our', $enhancedText);
            $enhancedText = str_ireplace('community', 'comrades', $enhancedText);
            $enhancedText = str_ireplace('suspicious', 'sus', $enhancedText);
            $enhancedText = ini_set('sus', 'font-weight: italic');
            $enhancedText = preg_replace_callback(['~!~', '~?~', '~.~'], function () use ($exclaiming) {
                $exclaimingIndex = rand(0, 5);
                return $exclaiming[$exclaimingIndex];
            }, $enhancedText);
            // $enhancedText = str_ireplace('o', $oh[rand(0, 1)], $enhancedText);
            $enhancedText = preg_replace_callback('/o/i', function () use ($o) {
                $ohIndex = rand(0, 1);
                return $o[$ohIndex];
            }, $enhancedText);
            // $enhancedText = preg_replace_callback('/r/i', function () use ($r) {
            //     $rIndex = rand(0, 1);
            //     return $r[$rIndex];
            // }, $enhancedText);
            // $enhancedText = preg_replace_callback('/f/i', function () use ($f) {
            //     $fIndex = rand(0, 2);
            //     return $f[$fIndex];
            // }, $enhancedText);
            // $enhancedText = preg_replace_callback('/e/i', function () use ($e) {
            //     $eIndex = rand(0, 2);
            //     return $e[$eIndex];
            // }, $enhancedText);
            return $enhancedText;
        } else if($algorithm === 7) {
            // whySayLotWordWhenFewWordDoTrick
            $enhancedText = str_ireplace([" are ", " is ", " was ", " were ", " would ", " I ", " I'd ", " I'll ", " I'm ", " to ", " a ", " an ", " but ", " the "], ' ', $enhancedText);
            $enhancedText =  str_replace(["'ll", "'re", "'ve", "'d", "'s", "'m"], '', $enhancedText);
            $enhancedText =  str_replace(["can't", "couldn't", "won't", "wouldn't", "shouldn't"], 'not', $enhancedText);
            // convert all words (in practice as it stands just a bunch of them) into their base form
            // first two replacements handles exceptions
            $enhancedText = str_replace('lying', 'lie', $enhancedText);
            $enhancedText = str_replace('lives', 'life', $enhancedText);
            // examples: liking = like
            $enhancedText = str_replace('iking', 'like', $enhancedText);
            if(strpbrk($enhancedText, 'iking ') || strpbrk($enhancedText, 'ting ')) {
                $enhancedText = str_replace('ing ', '', $enhancedText);
            }
            // examples: flying = fly and seeing = see
            $enhancedText = str_replace('ing', '', $enhancedText);
            // examples: hiding = hide and fading = fade
            $enhancedText = str_replace('ding', 'e', $enhancedText);
            // examples: tries = try and cries = cry
            $enhancedText = str_replace('ies', 'y', $enhancedText);
            // examples: finding = find and sending = send
            $enhancedText = str_replace('ish', '', $enhancedText);
            // examples: foolishness = fool and hopelessness = hopeless
            $enhancedText = str_replace('ishness ', '', $enhancedText);
            // example: greatness = great
            $enhancedText = str_replace('ness ', '', $enhancedText);
            // example: eagerly = eager
            $enhancedText = str_replace('ely ', 'er', $enhancedText);
            $enhancedText = str_replace('ers ', 'er', $enhancedText);
            // example: foolery = fool
            $enhancedText = str_replace('lery', '', $enhancedText);
            $enhancedText = str_replace('tly ', 'e ', $enhancedText);
            // examples: calmly = calm and begrudgingly = begrudging
            $enhancedText = str_replace('ly ', ' ', $enhancedText);
            $enhancedText = str_replace('gment', 'e', $enhancedText);
            $enhancedText = str_replace('gments', 'e', $enhancedText);
            // examples: confinement = confine and contentment = content
            $enhancedText = str_replace('ment ', ' ', $enhancedText);
            // examples: fumes = fume and comes = come
            $enhancedText = str_replace('mes ', 'me ', $enhancedText);
            // examples: onions = onion and exceptions = exception
            $enhancedText = str_replace('ons ', 'on ', $enhancedText);
            // examples: hives = hive and waves = wave
            $enhancedText = str_replace('ves ', 've ', $enhancedText);
            $enhancedText = str_replace('es ', ' ', $enhancedText);
            $enhancedText = str_replace('gs ', 'g ', $enhancedText);
            // examples: shows = show and lows = low
            $enhancedText = str_replace('ws', 'w', $enhancedText);
            $enhancedText = str_replace('hs', 'h', $enhancedText);
            // examples: finds = find and raids = raid
            $enhancedText = str_replace('ds', 'd', $enhancedText);
            // examples: heights = height and rights = right
            $enhancedText = str_replace('ts', 't', $enhancedText);
            // examples: mastery = master and wastery = waster
            $enhancedText = str_replace('tery', 'ter', $enhancedText);
            // examples: delightful = delight and spiteful = spite
            $enhancedText = str_replace('tful', 't', $enhancedText);
            // examples: spiteful = spite and hateful = hate
            $enhancedText = str_replace('eful', 'e', $enhancedText);
            return $enhancedText;
        } else if($algorithm === 8) {
            // botchedCharacterToKeyboardLayout
            $chrs = [];
            $enhancedChrs = [];
            for ($i = 0; $i<strlen($enhancedText); $i++) {
                $chrs[] = ord($enhancedText[$i]);
            }
            for ($i = 0; $i<count($chrs); $i++) {
                if($chrs[$i] > 64 && $chrs[$i] < 91 || $chrs[$i] > 96 && $chrs[$i] < 123) {
                    if (rand(0, 5) === 0) {
                        $chrs[$i] += rand(-1, 1);
                    }
                }
                $enhancedChrs[] = chr($chrs[$i]);
            }
            $enhancedText = implode('', $enhancedChrs);
            return $enhancedText;
        } else if($algorithm === 9) {
            // glyphLike (appears as circled characters (similar to a copyright circle) and has random word order with tabs for every new line or punctuation)
        } else if($algorithm === 10) {
            // glitchedInTransmission (looks very messy with every third or fifth word missing a random half of its characters)
        } else if($algorithm === 11) {
            // coherent? (randomizes sentence order and punctuation)
            $punctuation = [".", "...", "?", "!"];
            $enhancedText = preg_replace_callback(['~!~', '~?~', '~.~'], function () use ($punctuation) {
                $punctuationIndex = rand(0, 3);
                return $punctuation[$punctuationIndex];
            }, $enhancedText);
            return $enhancedText;
        } else if($algorithm === 12) {
            // weHaveYodaSpeakAtHome
            function verbsInTheEnd($sentence,$verbs) {
                $wordArray = explode(' ',$sentence);
                foreach($wordArray as $key => $word) {
                    if(in_array($word,$verbs)) {
                        unset($wordArray[$key]);
                        $wordArray[] = $word;
                    }
                }
                return implode(' ',$wordArray);
            }
            $verbs = array(
                'are',
                'were'
            );
            $sentence = 'you guys are great';
            return $enhancedText = verbsInTheEnd($sentence,$verbs);
            // Outputs "you guys great are"
        } else {
            throw new \Exception("Invalid text processing algorithm: $algorithm");
        }
    }
}
