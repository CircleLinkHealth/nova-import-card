<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UpdateBankHolidaysForYear extends Command
{
    const CHRISTMAS          = 'Christmas';
    const CHRISTMAS_DATE     = '12-25';
    const JULY_4TH           = 'July 4th';
    const JULY_4TH_DATE      = '07-04';
    const LABOR_DAY          = 'Labor Day';
    const MEMORIAL_DAY       = 'Memorial Day';
    const NEW_YEARS_DAY      = "New Year's day";
    const NEW_YEARS_DAY_DATE = '01-01';
    const THANKSGIVING       = 'Thanksgiving';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Company Holidays for given year. Values should be assigned in $this > bankHolidaysForYear().';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:bankHolidaysFor {--year=}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $year = $this->option('year');
        if ( ! $year) {
            $year = now()->toDateString();
        }
        $bankHolidays = $this->bankHolidaysForYear($year);

        foreach ($bankHolidays as $bankHoliday => $date) {
            $saved = DB::table('company_holidays')
                ->updateOrInsert([
                    'holiday_name' => $bankHoliday,
                    'holiday_date' => $date,
                ]);

            if ( ! $saved) {
                $this->info("Failed to save $bankHoliday");
            }
        }

        $this->info("Bank Holidays for $year saved!");
    }

    private function bankHolidaysForYear(string $year): Collection
    {
        $july4thConstantDate  = self::JULY_4TH_DATE;
        $xmasConstantDate     = self::CHRISTMAS_DATE;
        $newYearsConstantDate = self::NEW_YEARS_DAY_DATE;

        return collect([
            self::NEW_YEARS_DAY => "$year-$newYearsConstantDate",
            self::MEMORIAL_DAY  => "$year-05-31",
            self::JULY_4TH      => "$year-$july4thConstantDate",
            self::LABOR_DAY     => "$year-06-09",
            self::THANKSGIVING  => "$year-11-25",
            self::CHRISTMAS     => "$year-$xmasConstantDate",
        ]);
    }
}
