<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserAction extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_actions';
}
