<?php

use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrphanWorkHours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $today = now();
        $endOfYear = $today->copy()->endOfYear();

        WorkHours::where([
            ['work_week_start', '>', $today->copy()->startOfWeek()],
            ['work_week_start', '<', $endOfYear]
        ])->chunk(50, function ($workHours) use (&$windows, $today) {
            /** @var WorkHours $workHours */
            $workHours->each(function ($week) use (&$windows, $today) {
                $nurse = Nurse::whereId($week->workhourable_id)->firstOrFail();
                $nurseActiveAndNonDemo = $nurse->status === 'active' && !$nurse->is_demo;
                $dates = createWeekMap($week->work_week_start);
                if ($nurseActiveAndNonDemo) {
                    foreach ($dates as $date) {
                        $carbon = Carbon::parse($date)->copy();
                        $day = strtolower(clhDayOfWeekToDayName($carbon->dayOfWeek));
//                        IF workDay "day" has not 0 hours
                        if ($week[$day] > 0
                            && $carbon->gt($today->copy())) {
                            $window = NurseContactWindow::where('nurse_info_id', $week->workhourable_id)
                                ->where('date', $carbon)
                                ->first();

                            if (empty($window)) {
                                /** @var WorkHours $week */
                                $week->update(
                                    [
                                        $day => 0
                                    ]);
                            }
                        }

                    }
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
}
