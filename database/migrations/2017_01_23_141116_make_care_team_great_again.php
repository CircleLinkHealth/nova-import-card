<?php

use App\PatientCareTeamMember;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCareTeamGreatAgain extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PatientCareTeamMember::where('type', '=', PatientCareTeamMember::LEAD_CONTACT)
            ->delete();

        $careTeams = PatientCareTeamMember::get()->groupBy('user_id');
        $membersToDelete = [];

        foreach ($careTeams as $team) {
            $billingProvider = $team->where('type', '=', PatientCareTeamMember::BILLING_PROVIDER)->first();
            $members = $team->where('type', '=', PatientCareTeamMember::MEMBER)->reject(function ($member) use
            (
                $billingProvider,
                &
                $membersToDelete
            ) {
                if ($member->member_user_id == $billingProvider->member_user_id) {
                    $membersToDelete[] = $member->id;

                    return false;
                }

                return true;
            });
        }

        PatientCareTeamMember::whereIn('id', $membersToDelete)
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_care_team_members', function (Blueprint $table) {
            //
        });
    }
}
