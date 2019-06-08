<?php

namespace Qirolab\Laravel\Reactions\Traits;

use Illuminate\Database\Eloquent\Builder;
use Qirolab\Laravel\Reactions\Models\Reaction;
use Qirolab\Laravel\Reactions\Contracts\ReactsInterface;
use Qirolab\Laravel\Reactions\Exceptions\InvalidReactionUser;

trait Reactable
{
    /**
     * Collection of reactions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    /**
     * Get collection of reacters who reacted on reactable model.
     *
     * @param null $model
     * @param null $type
     * @return mixed
     */
    public function reactionsBy($model = null, $type = null)
    {
        $model = $model ?: $this->resolveUserModel();

        $reactions = $this->reactions;

        if($type){
            $reactions = $reactions->where('type', $type);
        }

        $ids = $reactions->pluck('reacter_id');

        return $model::whereIn('id', $ids)->get();
    }

    /**
     * Attribute to get collection of reacters who reacted on reactable model.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getReactionsByAttribute()
    {
        return $this->reactionsBy();
    }

    /**
     * Reaction summary.
     *
     * @param string $by
     * @return mixed
     */
    public function reactionSummary($by = 'type')
    {
        return $this->reactions->groupBy($by)->map(function ($val) {
            return $val->count();
        });
    }

    /**
     * Reaction summary attribute.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getReactionSummaryAttribute()
    {
        return $this->reactionSummary();
    }

    /**
     * Add reaction.
     *
     * @param $reactionType
     * @param null $model
     * @return bool|void
     */
    public function react($reactionType, $model = null)
    {
        $model = $model ?: $this->getUser();

        if ($model) {
            return $model->reactTo($this, $reactionType);
        }

        return false;
    }

    /**
     * Remove reaction.
     *
     * @param null $model
     * @return bool
     */
    public function removeReaction($model = null)
    {
        $model = $model ?: $this->getUser();

        if ($model) {
            return $model->removeReactionFrom($this);
        }

        return false;
    }

    /**
     * Toggle Reaction.
     *
     * @param $reactionType
     * @param null $model
     * @return mixed
     */
    public function toggleReaction($reactionType, $model = null)
    {
        $model = $model ?: $this->getUser();

        if ($model) {
            return $model->toggleReactionOn($this, $reactionType);
        }
    }

    /**
     * Reaction on reactable model by reacter.
     *
     * @param null $model
     * @return mixed
     */
    public function reacted($model = null)
    {
        $model = $model ?: $this->getUser();

        return $this->reactions
                    ->where(['reacter_id' => $model->id, 'reacter_type' => get_class($model)])
                    ->first();
    }

    /**
     * Reaction on reactable model by reacter.
     *
     * @return mixed
     */
    public function getReactedAttribute()
    {
        return $this->reacted();
    }

    /**
     * Check if a type is reacted by reacter.
     *
     * @param null $model
     * @param null $type
     * @return bool
     */
    public function isReactBy($model = null, $type = null)
    {
        $model = $model ?: $this->getUser();

        if ($model) {
            return $model->isReactedOn($this, $type);
        }

        return false;
    }

    /**
     * Check if a type is reacted by reacter.
     *
     * @return bool
     */
    public function getIsReactedAttribute()
    {
        return $this->isReactBy();
    }

    /**
     * Fetch records that are reacted by a given reacter.
     *
     * @todo think about method name
     * @param Builder $query
     * @param null $model
     * @param null $type
     * @return Builder
     *
     * @throw \Qirolab\Laravel\Reactions\Exceptions\InvalidReactionUser
     */
    public function scopeWhereReactedBy(Builder $query, $model = null, $type = null)
    {
        try {
            $model = $model ?: $this->getUser();
        } catch (InvalidReactionUser $e) {
            throw InvalidReactionUser::notDefined();
        }

        return $query->whereHas('reactions', function ($innerQuery) use ($model, $type) {
            $innerQuery->where('reacter_id', $model->id)
                        ->where('reacter_type', get_class($model));

            if ($type) {
                $innerQuery->where('type', $type);
            }
        });
    }

    /**
     * Get user model.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private function getUser()
    {
        if (auth()->check()) {
            return auth()->user();
        }

        throw InvalidReactionUser::notDefined();
    }

    /**
     * Retrieve User's model class name.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function resolveUserModel()
    {
        return config('auth.providers.users.model');
    }
}
