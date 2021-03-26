<?php

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class UpdateCorrectProvidersToEnrollees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:enrollee-providers {practiceId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the provider id of given enrollee ids.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getEnrolleesToUpdateGrouppedByProviders()
    {
        return collect($this->getEnrolleeProviderPairToUpdate())
            ->mapToGroups(function ($providerId, $enrolleeId){
                return [
                    $providerId => $enrolleeId
                ];
        });
    }

    /**
     *
     */
    public function handle()
    {
        $enrolleesToUpdateGrouppedByProvider =  $this->getEnrolleesToUpdateGrouppedByProviders();

        $enrollees = Enrollee::where('practice_id', $this->argument('practiceId'));

        $enrolleesToUpdateGrouppedByProvider->each(function ($enrolleeIds,$providerId) use($enrollees) {
            $this->info("Updating enrollees for Provider $providerId");
            $updated = $enrollees->whereIn('id', $enrolleeIds->toArray())
                ->update(
                    [
                        'provider_id' => $providerId
                    ]);

            $this->info("Enrollees for Provider $providerId updated: $updated");
        });
    }

    public function getEnrolleeProviderPairToUpdate()
    {
        return [
            '361542' => '50241',
            '361544' => '50244',
//            '361553' => 'Muhammad Shajaat',
            '361555' => '50244',
            '361558' => '50244',
            '361560' => '50244',
//            '361571' => 'Raul Ramirez',
//            '361573' => 'Patient Terminated from practice',
            '361594' => '50244',
            '361596' => '50243',
//            '361597' => 'Muhammad Shajaat',
            '361598' => '50244',
            '361602' => '50244',
            '361603' => '50244',
            '361605' => '50241',
            '361609' => '50244',
            '361610' => '50244',
            '361621' => '50245',
            '361629' => '50244',
            '361637' => '50244',
            '361638' => '50243',
            '361639' => '50244',
            '361644' => '50244',
            '361647' => '50244',
            '361653' => '50244',
//            '361656' => 'Raul Ramirez',
            '361679' => '50244',
            '361680' => '50244',
            '361681' => '50244',
            '361683' => '50244',
//            '361685' => 'Muhammad Shajaat',
            '361690' => '50244',
            '361693' => '50244',
            '361705' => '50244',
            '361714' => '50244',
            '361715' => '50244',
            '361717' => '50244',
            '361732' => '50244',
            '361740' => '50244',
            '361742' => '50244',
            '361744' => '50244',
            '361745' => '50244',
//            '361749' => 'Muhammad Shajaat',
            '361750' => '50244',
            '361754' => '50244',
            '361756' => '50241',
            '361767' => '50244',
//            '361769' => 'Muhammad Shajaat',
            '361775' => '50244',
            '361782' => '50244',
//            '361784' => 'Muhammad Shajaat',
            '361788' => '50243',
            '361793' => '50244',
            '361794' => '50244',
//            '361799' => 'Raul Ramirez',
            '361802' => '50244',
            '361803' => '50244',
            '361806' => '50244',
            '361809' => '50244',
            '361810' => '50244',
            '361811' => '50244',
            '361812' => '50244',
            '361815' => '50244',
            '361816' => '50244',
            '361819' => '50243',
            '361823' => '50244',
            '361825' => '50240',
            '361826' => '50244',
            '361841' => '50244',
            '361845' => '50244',
            '361852' => '50244',
            '361859' => '50244',
            '361862' => '50244',
            '361867' => '50243',
            '361876' => '50244',
            '361881' => '50244',
            '361882' => '50244',
            '361892' => '50244',
            '361898' => '50244',
            '361908' => '50244',
            '361910' => '50244',
            '361920' => '50244',
            '361923' => '50244',
            '361924' => '50244',
            '361945' => '50244',
            '361946' => '50244',
            '361948' => '50244',
            '361949' => '50244',
            '361951' => '50244',
            '361958' => '50244',
//            '361964' => 'Muhammad Shajaat',
            '361965' => '50244',
//            '361966' => 'Muhammad Shajaat',
            '361967' => '50244',
            '361969' => '50241',
            '361970' => '50244',
            '361975' => '50244',
            '361983' => '50244',
            '361985' => '50244',
            '361996' => '50243',
            '362006' => '50244',
            '362009' => '50244',
//            '362015' => 'Raul Ramirez',
            '362024' => '50244',
            '362027' => '50244',
            '362028' => '50244',
            '362029' => '50243',
            '362032' => '50244',
            '362034' => '50244',
//            '381234' => 'Raul Ramirez',
            '381241' => '50244',
//            '381245' => 'Muhammad Shajaat',
            '381261' => '50244',
//            '381265' => 'Raul Ramirez',
            '381266' => '50244',
            '381269' => '50244',
            '381276' => '50244',
            '381277' => '50244',
//            '381284' => 'Muhammad Shajaat',
            '381287' => '50244',
            '381294' => '50244',
//            '381297' => 'Muhammad Shajaat',
            '381298' => '50244',
            '381300' => '50244',
//            '381303' => 'Muhammad Shajaat',
            '381307' => '50244',
            '381310' => '50243',
//            '381315' => 'Muhammad Shajaat',
            '381318' => '50244',
            '381323' => '50244',
//            '381327' => 'Muhammad Shajaat',
            '381331' => '50244',
            '381337' => '50244',
            '381341' => '50244',
            '381345' => '50244',
//            '381351' => 'Muhammad Shajaat',
//            '381352' => 'Muhammad Shajaat',
            '381364' => '50244',
            '381366' => '50244',
            '381367' => '50241',
            '381369' => '50244',
            '381370' => '50244',
            '381375' => '50244',
            '381376' => '50244',
            '381379' => '50244',
            '381383' => '50244',
//            '381387' => 'Muhammad Shajaat',
            '381389' => '50244',
            '381398' => '50244',
            '381407' => '50244',
//            '381409' => 'Muhammad Shajaat',
            '381418' => '50244',
            '381430' => '50244',
            '381443' => '50244',
            '381479' => '50244',
            '381480' => '50244',
            '381493' => '50244',
//            '381537' => 'Raul Ramirez',
        ];
    }
}



















































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































