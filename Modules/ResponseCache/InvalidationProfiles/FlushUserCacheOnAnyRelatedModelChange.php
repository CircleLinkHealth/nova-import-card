<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\InvalidationProfiles;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Event;
use Illuminate\Database\Eloquent\Model;

class FlushUserCacheOnAnyRelatedModelChange
{
    private $cacheTag;
    private $invalidationCandidates = [];

    /**
     * @param array $invalidationCandidates
     */
    public function addInvalidationCandidate(int $userId): void
    {
        $this->invalidationCandidates[] = $userId;
    }

    public function flush($users)
    {
        foreach (parseIds($users) as $userId) {
            $cacheTags[] = $this->getTagForUser($userId);
        }

        if ( ! empty($cacheTags)) {
            $flushed = \Cache::tags($cacheTags)->flush();

            return [
                'success' => $flushed ?? empty($cacheTags),
                'tags'    => $cacheTags ?? [],
            ];
        }
        foreach (\Redis::keys('*responsecache*') as $key) {
            $cacheTags[$key] = \Redis::del($key);
        }

        return $cacheTags ?? [];
    }

    public function flushCandidates()
    {
        foreach (array_unique($this->getInvalidationCandidates()) as $userId) {
            $cleared[$userId] = $this->flushCache($userId);
        }

        return $cleared ?? [];
    }

    /**
     * @return array
     */
    public function getInvalidationCandidates(): array
    {
        return $this->invalidationCandidates;
    }

    public function registerEloquentEventListener()
    {
        //Clear responsecache every time a model is created, updated, or deleted
        Event::listen(
            ['eloquent.created: *', 'eloquent.updated: *', 'eloquent.deleted: *'],
            function ($event, array $models) {
                foreach ($models as $model) {
                    foreach ($this->userId($model) as $userId) {
                        $this->addInvalidationCandidate($userId);
                    }
                }
            }
        );
    }

    /**
     * Flush the cache collection of the given user.
     *
     * @param $userId
     *
     * @return bool
     */
    private function flushCache($userId)
    {
        return \Cache::tags($this->getTagForUser($userId))->flush();
    }

    private function getCacheTag()
    {
        if ( ! $this->cacheTag) {
            $this->cacheTag = config('responsecache.cache_tag').'user_';
        }

        return $this->cacheTag;
    }

    private function getTagForUser(int $userId)
    {
        return $this->getCacheTag().$userId;
    }

    private function getUsersFromRelationship(Model $model, string $relation)
    {
        if ( ! $this->hasRelationship($model, $relation)) {
            return [];
        }

        $relationType = null;
        $userIds      = [];

        $model->{$relation}()->distinct()->chunk(
            50,
            function ($models) use (&$relationType, &$userIds) {
                foreach ($models as $model) {
                    if (null === $relationType) {
                        $relationType = get_class($model);
                    }
                    if ( ! in_array($relationType, [User::class, Patient::class])) {
                        //break out of chunk closure
                        return false;
                    }

                    if (User::class === $relationType) {
                        $userIds[] = parseIds($model);
                    }

                    if (Patient::class === $relationType && ! empty($model->user_id)) {
                        $userIds[] = $model->user_id;
                    }
                }
            }
        );

        return array_unique($userIds);
    }

    /**
     * Determine if the given relationship (method) exists.
     *
     * @param string $relation
     *
     * @return bool
     */
    private function hasRelationship(Model $model, string $relation)
    {
        if ($model->relationLoaded($relation)) {
            return true;
        }

        if (method_exists($model, $relation)) {
            return is_a($model->$relation(), "Illuminate\Database\Eloquent\Relations\Relation");
        }

        return false;
    }

    /**
     * Get a user ID from the surrounding ecosystem without disturbing the plants.
     *
     * The function checks model for:
     *  - patient_id attribute
     *  - user_id attribute
     *  - id attribute if model is of class User
     *  - model has any of those relations that yield user IDs: patient, patients, user, users
     *
     * @param Model $model
     *
     * @return array
     */
    private function userId(Model $model): array
    {
        if (($userId = parseIds($model->patient_id ?? $model->user_id)) && ! empty($userId)) {
            return $userId;
        }
        if (is_a($model, User::class) && ($userId = parseIds($model)) && ! empty($userId)) {
            return $userId;
        }

        foreach ([
            'patient',
            'patients',
            'user',
            'users',
            'patient_info',
            'patientInfo',
        ] as $relation) {
            $userIds = $this->getUsersFromRelationship($model, $relation);
            if ( ! empty($userIds)) {
                return $userIds;
            }
        }

        return [];
    }
}
