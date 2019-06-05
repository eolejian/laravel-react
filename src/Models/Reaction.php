<?php

namespace Qirolab\Laravel\Reactions\Models;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reactions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reacter_id',
        'reacter_type',
        'type',
    ];

    /**
     * Reactable model relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function reactable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that reacted on reactable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function reactBy($model = null)
    {
        $model = $model ?: config('auth.providers.users.model');
        
        return $this->belongsTo($model, 'reacter_id');
    }
}
