<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\CPRulesAnswers.
 *
 * @property int         $aid
 * @property string      $value
 * @property string|null $alt_answers
 * @property int|null    $a_sort
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers whereASort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers whereAid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers whereAltAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers whereValue($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers query()
 */
class CPRulesAnswers extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['value', 'alt_answers', 'a_sort'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'aid';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_answers';
}
