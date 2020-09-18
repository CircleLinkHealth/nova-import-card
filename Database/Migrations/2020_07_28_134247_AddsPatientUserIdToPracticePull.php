<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsPatientUserIdToPracticePull extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('practice_pull_demographics', 'patient_user_id')) {
            Schema::table('practice_pull_demographics', function (Blueprint $table) {
                $table->unsignedInteger('patient_user_id')->nullable()->after('practice_id');
                $table->foreign('patient_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('set null');
            });

            Demographics::whereNull('patient_user_id')
                ->whereIn('mrn', function ($q) {
                    $q->select('mrn_number')->from('patient_info')->whereIn('user_id', function ($q) {
                        $q->select('id')->from('users')->whereIn('program_id', [235, 344]);
                    });
                })
                ->chunkById(500, function ($demos) {
                    DB::transaction(function () use ($demos) {
                        foreach ($demos as $d) {
                            $d->patient_user_id = Patient::whereHas('user', function ($q) use ($d) {
                                $q->ofPractice($d->practice_id)->ofType(['participant', 'survey-only']);
                            })->where('mrn_number', $d->mrn)->whereNotNull('mrn_number')->value('user_id');

                            if ($d->patient_user_id) {
                                $d->save();
                            }
                        }
                    });
                });
        }
    }
}
