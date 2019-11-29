<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Venturecraft\Revisionable;

/*
 * This file is part of the Revisionable package by Venture Craft
 *
 * (c) Venture Craft <http://www.venturecraft.com.au>
 *
 */

/**
 * Class RevisionableTrait.
 */
trait RevisionableTrait
{
    /**
     * Keeps the list of values that have been updated.
     *
     * @var array
     */
    protected $dirtyData = [];

    /**
     * @var array
     */
    private $doKeep = [];

    /**
     * @var array
     */
    private $dontKeep = [];
    /**
     * @var array
     */
    private $originalData = [];

    /**
     * @var array
     */
    private $updatedData = [];

    /**
     * @var bool
     */
    private $updating = false;

    /**
     * Ensure that the bootRevisionableTrait is called only
     * if the current installation is a laravel 4 installation
     * Laravel 5 will call bootRevisionableTrait() automatically.
     */
    public static function boot()
    {
        parent::boot();

        if ( ! method_exists(get_called_class(), 'bootTraits')) {
            static::bootRevisionableTrait();
        }
    }

    /**
     * Create the event listeners for the saving and saved events
     * This lets us save revisions whenever a save is made, no matter the
     * http method.
     */
    public static function bootRevisionableTrait()
    {
        static::saving(function ($model) {
            $model->preSave();
        });

        static::saved(function ($model) {
            $model->postSave();
        });

        static::created(function ($model) {
            $model->postCreate();
        });

        static::deleted(function ($model) {
            $model->preSave();
            $model->postDelete();
        });
    }

    /**
     * Generates a list of the last $limit revisions made to any objects of the class it is being called from.
     *
     * @param int    $limit
     * @param string $order
     *
     * @return mixed
     */
    public static function classRevisionHistory($limit = 100, $order = 'desc')
    {
        $model = Revisionable::newModel();

        return $model->where('revisionable_type', get_called_class())
            ->orderBy('updated_at', $order)->limit($limit)->get();
    }

    /**
     * Disable a revisionable field temporarily
     * Need to do the adding to array longhanded, as there's a
     * PHP bug https://bugs.php.net/bug.php?id=42030.
     *
     * @param mixed $field
     */
    public function disableRevisionField($field)
    {
        if ( ! isset($this->dontKeepRevisionOf)) {
            $this->dontKeepRevisionOf = [];
        }
        if (is_array($field)) {
            foreach ($field as $one_field) {
                $this->disableRevisionField($one_field);
            }
        } else {
            $donts                    = $this->dontKeepRevisionOf;
            $donts[]                  = $field;
            $this->dontKeepRevisionOf = $donts;
            unset($donts);
        }
    }

    /**
     * @return mixed
     */
    public function getRevisionFormattedFieldNames()
    {
        return $this->revisionFormattedFieldNames;
    }

    /**
     * @return mixed
     */
    public function getRevisionFormattedFields()
    {
        return $this->revisionFormattedFields;
    }

    /**
     * Revision Unknown String
     * When displaying revision history, when a foreign key is updated
     * instead of displaying the ID, you can choose to display a string
     * of your choice, just override this method in your model
     * By default, it will fall back to the models ID.
     *
     * @return string an identifying name for the model
     */
    public function getRevisionNullString()
    {
        return isset($this->revisionNullString) ? $this->revisionNullString : 'nothing';
    }

    /**
     * No revision string
     * When displaying revision history, if the revisions value
     * cant be figured out, this is used instead.
     * It can be overridden.
     *
     * @return string an identifying name for the model
     */
    public function getRevisionUnknownString()
    {
        return isset($this->revisionUnknownString) ? $this->revisionUnknownString : 'unknown';
    }

    /**
     * Attempt to find the user id of the currently logged in user
     * Supports Cartalyst Sentry/Sentinel based authentication, as well as stock Auth.
     */
    public function getSystemUserId()
    {
        try {
            if (class_exists($class = '\SleepingOwl\AdminAuth\Facades\AdminAuth')
                || class_exists($class = '\Cartalyst\Sentry\Facades\Laravel\Sentry')
                || class_exists($class = '\Cartalyst\Sentinel\Laravel\Facades\Sentinel')
            ) {
                return ($class::check()) ? $class::getUser()->id : null;
            }
            if (\Auth::check()) {
                return \Auth::user()->getAuthIdentifier();
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Get the IP of the User who initiated the revision.
     *
     * @return string|null
     */
    public function getUserIpAddress()
    {
        return \Request::getClientIp();
    }

    /**
     * Identifiable Name
     * When displaying revision history, when a foreign key is updated
     * instead of displaying the ID, you can choose to display a string
     * of your choice, just override this method in your model
     * By default, it will fall back to the models ID.
     *
     * @return string an identifying name for the model
     */
    public function identifiableName()
    {
        return $this->getKey();
    }

    /**
     * Called after record successfully created.
     */
    public function postCreate()
    {
        // Check if we should store creations in our revision history
        // Set this value to true in your model if you want to
        if (empty($this->revisionCreationsEnabled)) {
            // We should not store creations.
            return false;
        }

        if (( ! isset($this->revisionEnabled) || $this->revisionEnabled)) {
            $revisions[] = [
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id'   => $this->getKey(),
                'key'               => self::CREATED_AT,
                'old_value'         => null,
                'new_value'         => $this->{self::CREATED_AT},
                'user_id'           => $this->getSystemUserId(),
                'ip'                => $this->getUserIpAddress(),
                'created_at'        => new \DateTime(),
                'updated_at'        => new \DateTime(),
            ];

            $revision = Revisionable::newModel();
            \DB::table($revision->getTable())->insert($revisions);
            \Event::dispatch('revisionable.created', ['model' => $this, 'revisions' => $revisions]);
        }
    }

    /**
     * If softdeletes are enabled, store the deleted time.
     */
    public function postDelete()
    {
        if (( ! isset($this->revisionEnabled) || $this->revisionEnabled)
            && $this->isSoftDelete()
            && $this->isRevisionable($this->getDeletedAtColumn())
        ) {
            $revisions[] = [
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id'   => $this->getKey(),
                'key'               => $this->getDeletedAtColumn(),
                'old_value'         => null,
                'new_value'         => $this->{$this->getDeletedAtColumn()},
                'user_id'           => $this->getSystemUserId(),
                'ip'                => $this->getUserIpAddress(),
                'created_at'        => new \DateTime(),
                'updated_at'        => new \DateTime(),
            ];
            $revision = Revisionable::newModel();
            \DB::table($revision->getTable())->insert($revisions);
            \Event::dispatch('revisionable.deleted', ['model' => $this, 'revisions' => $revisions]);
        }
    }

    /**
     * Called after a model is successfully saved.
     */
    public function postSave()
    {
        if (isset($this->historyLimit) && $this->revisionHistory()->count() >= $this->historyLimit) {
            $LimitReached = true;
        } else {
            $LimitReached = false;
        }
        if (isset($this->revisionCleanup)) {
            $RevisionCleanup = $this->revisionCleanup;
        } else {
            $RevisionCleanup = false;
        }

        // check if the model already exists
        if ((( ! isset($this->revisionEnabled) || $this->revisionEnabled) && $this->updating) && ( ! $LimitReached || $RevisionCleanup)) {
            // if it does, it means we're updating

            $changes_to_record = $this->changedRevisionableFields();

            $revisions = [];

            foreach ($changes_to_record as $key => $change) {
                $revisions[] = [
                    'revisionable_type' => $this->getMorphClass(),
                    'revisionable_id'   => $this->getKey(),
                    'key'               => $key,
                    'old_value'         => array_get($this->originalData, $key),
                    'new_value'         => $this->updatedData[$key],
                    'user_id'           => $this->getSystemUserId(),
                    'ip'                => $this->getUserIpAddress(),
                    'created_at'        => new \DateTime(),
                    'updated_at'        => new \DateTime(),
                ];
            }

            if (count($revisions) > 0) {
                if ($LimitReached && $RevisionCleanup) {
                    $toDelete = $this->revisionHistory()->orderBy('id', 'asc')->limit(count($revisions))->get();
                    foreach ($toDelete as $delete) {
                        $delete->delete();
                    }
                }
                $revision = Revisionable::newModel();
                \DB::table($revision->getTable())->insert($revisions);
                \Event::dispatch('revisionable.saved', ['model' => $this, 'revisions' => $revisions]);
            }
        }
    }

    /**
     * Invoked before a model is saved. Return false to abort the operation.
     *
     * @return bool
     */
    public function preSave()
    {
        if ( ! isset($this->revisionEnabled) || $this->revisionEnabled) {
            // if there's no revisionEnabled. Or if there is, if it's true

            $this->originalData = $this->original;
            $this->updatedData  = $this->attributes;

            // we can only safely compare basic items,
            // so for now we drop any object based items, like DateTime
            foreach ($this->updatedData as $key => $val) {
                if ('object' == gettype($val) && ! method_exists($val, '__toString')) {
                    unset($this->originalData[$key], $this->updatedData[$key]);

                    array_push($this->dontKeep, $key);
                }
            }

            // the below is ugly, for sure, but it's required so we can save the standard model
            // then use the keep / dontkeep values for later, in the isRevisionable method
            $this->dontKeep = isset($this->dontKeepRevisionOf) ?
                array_merge($this->dontKeepRevisionOf, $this->dontKeep)
                : $this->dontKeep;

            $this->doKeep = isset($this->keepRevisionOf) ?
                array_merge($this->keepRevisionOf, $this->doKeep)
                : $this->doKeep;

            unset($this->attributes['dontKeepRevisionOf'], $this->attributes['keepRevisionOf']);

            $this->dirtyData = $this->getDirty();
            $this->updating  = $this->exists;
        }
    }

    /**
     * @return mixed
     */
    public function revisionHistory()
    {
        return $this->morphMany(get_class(Revisionable::newModel()), 'revisionable');
    }

    /**
     * Get all of the changes that have been made, that are also supposed
     * to have their changes recorded.
     *
     * @return array fields with new data, that should be recorded
     */
    private function changedRevisionableFields()
    {
        $changes_to_record = [];
        foreach ($this->dirtyData as $key => $value) {
            // check that the field is revisionable, and double check
            // that it's actually new data in case dirty is, well, clean
            if ($this->isRevisionable($key) && ! is_array($value)) {
                if ( ! array_key_exists($key, $this->originalData) || $this->originalData[$key] != $this->updatedData[$key]) {
                    $changes_to_record[$key] = $value;
                }
            } else {
                // we don't need these any more, and they could
                // contain a lot of data, so lets trash them.
                unset($this->updatedData[$key], $this->originalData[$key]);
            }
        }

        return $changes_to_record;
    }

    /**
     * Check if this field should have a revision kept.
     *
     * @param string $key
     *
     * @return bool
     */
    private function isRevisionable($key)
    {
        // If the field is explicitly revisionable, then return true.
        // If it's explicitly not revisionable, return false.
        // Otherwise, if neither condition is met, only return true if
        // we aren't specifying revisionable fields.
        if (isset($this->doKeep) && in_array($key, $this->doKeep)) {
            return true;
        }
        if (isset($this->dontKeep) && in_array($key, $this->dontKeep)) {
            return false;
        }

        return empty($this->doKeep);
    }

    /**
     * Check if soft deletes are currently enabled on this model.
     *
     * @return bool
     */
    private function isSoftDelete()
    {
        // check flag variable used in laravel 4.2+
        if (isset($this->forceDeleting)) {
            return ! $this->forceDeleting;
        }

        // otherwise, look for flag used in older versions
        if (isset($this->softDelete)) {
            return $this->softDelete;
        }

        return false;
    }
}
