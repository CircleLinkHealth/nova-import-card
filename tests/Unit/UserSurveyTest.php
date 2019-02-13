<?php

namespace Tests\Unit;

use App\Survey;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserSurveyTest extends TestCase
{
    private $user;
    private $survey;
    private $surveyInstance;

    use DatabaseTransactions;

    /**
     * A user can have multiple surveys, and each survey can have multiple instances, with variable statuses of completion
     * for that user
     *
     *
     * @return void
     */
    public function test_user_has_surveys()
    {
       $this->attachSurveyToUser();

        //assert user can have many surveys
        $secondSurvey         = Survey::create([
            'name'        => Survey::VITALS,
            'description' => 'This is the second test description',
        ]);

        $secondSurveyInstance = SurveyInstance::create([
            'survey_id'  => $secondSurvey->id,
            'name'       => 'Vitals 2020',
            'start_date' => Carbon::now()->startOfYear(),
            'end_date'   => Carbon::now()->endOfYear(),
        ]);

        $this->user->surveys()->attach(
            $secondSurvey->id,
            [
                'survey_instance_id' => $secondSurveyInstance->id,
                'status'             => SurveyInstance::COMPLETED,
            ]);

        $this->assertEquals($this->user->surveys()->count(), 2);
        $this->assertEquals($this->user->surveys()->orderByDesc('id')->first()->pivot->status, SurveyInstance::COMPLETED );


    }

    /**
     * A survey can have multiple instances
     *
     */
    public function test_survey_has_survey_instances(){

        $instance = $this->survey->instances()->first();

        $this->assertNotNull($instance);
        $this->assertEquals($instance->name, 'HRA 2019');

        //assert survey can have many instances
        $this->survey->instances()->create([
            'name'       => 'TEST 2020',
            'start_date' => Carbon::now()->addYear(1)->startOfYear(),
            'end_date'   => Carbon::now()->addYear(1)->endOfYear(),
        ]);

        $this->assertEquals($this->survey->instances()->count(), 2);

    }

    /**
     *Testing inverse of relationship on survey instance
     */
    public function test_survey_instance_belongs_to_surveys_and_users(){

        $this->attachSurveyToUser();

        $user = $this->surveyInstance->users()->first();
        $survey = $this->surveyInstance->survey;

        $this->assertEquals($survey->id, $this->survey->id);
        $this->assertEquals($user->id, $this->user->id);
        $this->assertEquals($user->surveys()->first()->pivot->status, SurveyInstance::PENDING);

    }

    public function test_user_survey_instance_status_can_be_updated(){

        $this->attachSurveyToUser();

        $survey = $this->user->surveys()->first();
        //TODO: FIND A WAY TO USE updateExistingPivot IF IT EXISTS
        $survey->pivot->status = SurveyInstance::COMPLETED;
        $survey->save();
        $this->assertEquals($survey->pivot->status, SurveyInstance::COMPLETED);
    }

    public function attachSurveyToUser(){

        $this->user->surveys()->attach(
            $this->survey->id,
            [
                'survey_instance_id' => $this->surveyInstance->id,
                'status'             => SurveyInstance::PENDING,
            ]);

        $survey = $this->user->surveys()->first();

        $this->assertNotNull($survey);
        $this->assertEquals($survey->pivot->status, SurveyInstance::PENDING);
        $this->assertEquals($survey->pivot->survey_instance_id, $this->surveyInstance->id);
    }




    public function setUp()
    {
        parent::setUp();

        $this->user = User::create([
            'email'    => 'test@test.com',
            'name'     => 'Test',
            'password' => bcrypt('test'),
        ]);

        $this->survey         = Survey::create([
            'name'        =>  Survey::HRA,
            'description' => 'This is a test description',
        ]);

        $this->surveyInstance = SurveyInstance::create([
            'survey_id'  => $this->survey->id,
            'name'       => 'HRA 2019',
            'start_date' => Carbon::now()->startOfYear(),
            'end_date'   => Carbon::now()->endOfYear(),
        ]);


    }
}
