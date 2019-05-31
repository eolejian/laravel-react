<?php

namespace Qirolab\Laravel\Reactions\Traits;

use Qirolab\Laravel\Reactions\Models\Reaction;
use Qirolab\Laravel\Reactions\Events\OnReaction;
use Qirolab\Laravel\Reactions\Events\OnDeleteReaction;
use Qirolab\Laravel\Reactions\Contracts\ReactableInterface;

trait Reacts
{
    /**
     * Reaction on reactable model.
     *
     * @param ReactableInterface $reactable
     * @param $type
     * @return mixed|Reaction
     * @throws \Exception
     */
    public function reactTo(ReactableInterface $reactable, $type)
    {
        $reaction = $this->getReaction($reactable, $type);

        if (! $reaction) {
            return $this->storeReaction($reactable, $type);
        }

        if ($reaction->type == $type) {
            return $reaction;
        }

        $this->deleteReaction($reaction, $reactable);

        return $this->storeReaction($reactable, $type);
    }

    /**
     * Remove reaction from reactable model.
     *
     * @param ReactableInterface $reactable
     * @throws \Exception
     */
    public function removeReactionFrom(ReactableInterface $reactable)
    {
        $reaction = $this->getReaction($reactable);

        if (! $reaction) {
            return;
        }

        $this->deleteReaction($reaction, $reactable);
    }

    /**
     * Toggle reaction on reactable model.
     *
     * @param ReactableInterface $reactable
     * @param $type
     * @return Reaction|void
     * @throws \Exception
     */
    public function toggleReactionOn(ReactableInterface $reactable, $type)
    {
        $reaction = $this->getReaction($reactable, $type);

        if (! $reaction) {
            return $this->storeReaction($reactable, $type);
        }

        $this->deleteReaction($reaction, $reactable);

        if ($reaction->type == $type) {
            return;
        }

        return $this->storeReaction($reactable, $type);
    }

    /**
     * Reaction on reactable model.
     *
     * @param  ReactableInterface $reactable
     * @return Reaction
     */
    public function reactedOn(ReactableInterface $reactable)
    {
        return $reactable->reacted($this);
    }

    /**
     * Check is reacted on reactable model.
     *
     * @param  ReactableInterface $reactable
     * @param  mixed              $type
     * @return bool
     */
    public function isReactedOn(ReactableInterface $reactable, $type = null)
    {
        $isReacted = $reactable->reactions()->where([
            'user_id' => $this->getKey(),
        ]);

        if ($type) {
            $isReacted->where([
                'type' => $type,
            ]);
        }

        return $isReacted->exists();
    }

    /**
     * Store reaction.
     *
     * @param ReactableInterface $reactable
     * @param $type
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function storeReaction(ReactableInterface $reactable, $type)
    {
        $reaction = $reactable->reactions()->create([
            'user_id' => $this->getKey(),
            'type' => $type,
        ]);

        event(new OnReaction($reactable, $reaction, $this));

        return $reaction;
    }

    /**
     * Delete reaction.
     *
     * @param Reaction $reaction
     * @param ReactableInterface $reactable
     * @return bool|null
     * @throws \Exception
     */
    protected function deleteReaction(Reaction $reaction, ReactableInterface $reactable)
    {
        $response = $reaction->delete();

        event(new OnDeleteReaction($reactable, $reaction, $this));

        return $response;
    }

    /**
     * @param ReactableInterface $reactable
     * @param null $type
     * @return mixed
     */
    private function getReaction(ReactableInterface $reactable, $type = null)
    {
        $query_array = ['user_id' => $this->getKey()];

        if(!! $type){
            $query_array = array_merge($query_array,['type' => $type]);
        }

        return $reactable->reactions()->where($query_array)->first();
    }
}
