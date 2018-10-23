<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 28/09/2018
 * Time: 13:16
 */

namespace App;

class WT1CsvParser
{

    /**
     * @var
     */
    private $patients;

    /**
     * WT1CsvParser constructor.
     */
    public function __construct()
    {
        $this->patients = [];
    }

    public function parseFile(String $fileName)
    {
        $arr = parseCsvToArray($fileName);
        $this->parseCsvArray($arr);
    }

    public function parseCsvArray($arr)
    {
        foreach ($arr as $row) {
            $this->addToResult($row);
        }
    }

    public function toArray()
    {
        return $this->patients;
    }

    public function toJson()
    {
        return json_encode($this->patients);
    }

    /**
     * Parse a CSV row and add to result of patients.
     * NOTE: using plain PHP instead of Eloquent for better performance.
     *
     * @param $row array
     */
    private function addToResult($row)
    {
        $patientId = $row['patient_id'];
        if ( ! isset($this->patients[$patientId])) {
            $this->patients[$patientId] = [];
        }

        $entry = $this->patients[$patientId];

        //exit if problem already processed
        if (isset($entry['problems'])) {
            $problems   = $entry['problems'];
            $rowProblem = $row['description'];
            foreach ($problems as $problem) {
                if ($problem['name'] === $rowProblem) {
                    return;
                }
            }
        }

        if ( ! isset($entry['insurance_plans'])) {
            $entry['insurance_plans'] = []; //we want this to be translated to { "primary" : {}, "secondary": {} }
        }

        if ( ! isset($entry['problems'])) {
            $entry['problems'] = []; //we want this to be translated to [{}]
        }

        if ( ! isset($entry['medications'])) {
            $entry['medications'] = []; //we want this to be translated to [{}]
        }

        if ( ! isset($entry['allergies'])) {
            $entry['allergies'] = []; //we want this to be translated to [{}]
        }

        $entry['patient_id'] = $patientId;

        //medicalrecordnumber was in first csv
        $entry['mrn'] = $this->getValue($row, 'medicalrecordnumber') ?? $this->getValue($row, 'mrn');

        $entry['last_name']      = $this->getValue($row, 'lastname');
        $entry['first_name']     = $this->getValue($row, 'firstname');
        $entry['middle_name']    = $this->getValue($row, 'middlename');
        $entry['date_of_birth']  = $this->getValue($row, 'dob');
        $entry['address_line_1'] = $this->getValue($row, 'addr1');
        $entry['address_line_2'] = $this->getValue($row, 'addr2');
        $entry['city']           = $this->getValue($row, 'city');
        $entry['state']          = $this->getValue($row, 'state');
        $entry['postal_code']    = $this->getValue($row, 'zip');
        $entry['primary_phone']  = $this->getValue($row, 'phonehome');
        $entry['cell_phone']     = $this->getValue($row, 'phonecell');

        //datecreated was in first csv - not found in latest csv
        $entry['last_visit'] = $this->getValue($row, 'encounterdate') ?? $this->getValue($row, 'datecreated');
        
        $entry['preferred_provider'] = $this->getProviderValue($row);

        //FamHxAllergies,FamHxDiabetesII,FamHxCOPD,FamHxTuberculosis,FamHxHTN,FamHxHeartDisease,FamHxStroke,FamHxBleeding,FamHxMigraine,FamHxPsychiatric,FamHxDepression,FamHxAlcoholAbuse,FamHxArthritis,FamHxOsteoporosis,FamHxCancer,FamHxColonPolyps,FamHxColonCancer,FamHxKidneyDisease,FamHxLiverDisease,FamHxGERD,FamHxHeartAttack,FamHxHighChol,FamHxThyroidDisease,FamHxOther,FamHxAsthma,FamHxBreastCancer,FamHxCervicalCancer,FamHxLungCancer,FamHxOtherCancer,FamHxProstateCancer,FamHxDementia,FamHxHearingLoss,FamHxHuntingtons,FamHxParkinsons,FamHxSeizures,FamHxGlaucoma,FamHxMacularDeg
        //ReportedDiabetes,ReportedOverweight,ReportedPoorVision,ReportedHearing,ReportedDizziness,ReportedFalls,ReportedForgetfullness,ReportedDepression,ReportedSleeping,ReportedJointPain,ReportedHeart,ReportedHighBP,ReportedCAD,ReportedStroke,ReportedOsteoporosis,ReportedArthritis,ReportedADDADHD,ReportedAnxity,ReportedAspergers,ReportedBackPain,ReportedBiPolar,ReportedCancer,ReportedCHDCF,ReportedDementia,ReportedGERD,ReportingHearingProb,ReportedAfib,ReportedAngina,ReportedAtherosclerosis,ReportedCardiomyopathy,ReportedHeartOther,ReportedPAD,ReportedPVD,ReportedValveDisease,ReportedHepatitis,ReportedHIVAIDs,ReportedHxHeartAttack,ReportedIncontinence,ReportedInpatientLastMonth,ReportedInpatientLastYear,ReportedKidneyTrans,ReportedMovementDis,ReportedOCD,ReportedOtherMH,ReportedRA,ReportedSchizophrenia,ReportedSeizures,ReportedStrokeIsc,ReportedStrokeTIA,ReportedStrokeHem,ReportedUrinary,ReportedAsthma,ReportedCOPD,ReportedAsthmaCOPD,ReportedOther1,ReportedOther2,ReportedOther3,ReportedOther4,ReportedOther5,MacularLeftEye,MacularRightEye
        //ServingsNuts,ServingsFruit,ServingsFish,ServingsMeat,ServingsVegetables,ServingsBeverages,ServingsFriedFood,ServingsSaturatedFat
        //ROSWeightGain,ROSWeightLoss,ROSMalaise,ROSFever,ROSChills,ROSFatigue,ROSWeakness,ROSAllergies,ROSAppetite,ROSDizziness,ROSInsomnia,ROSSOB,ROSWheezing,ROSVertigo,ROSBackPain,ROSJointPain,ROSProbSleep,ROSForgetful,ROSVisionProb,ROSHearingProb,ROSFalling,ROSSnoring,ROSChoking,ROSTremors,ROSCough,ROSEyePain,ROSHalos,ROSBleeding,ROSLegPain,ROSChestPain,ROSNumbness,ROSStiffness,ROSSwelling,ROSOther
        //FCSitting,FCStanding,FCLifting,FCCarrying,FCPushing,FCPulling,FCBending,FCStooping,FCSquatting,FCKneeling,FCReaching,FCHandUse
        //SAHandrails,SASlippery,SADriving,SALocking,SAFamily,SAFriends
        //MCIEye,MCIWalking,MCITouch,MCIClock,MCILocation,MCICopy,MCI3Step,MCIListofWords,MCIWritten,MCIWrite,MCICount
        //DSHappyFamily,DSOutlook,DSDepression,DSSadness,DSActivities,DSWeight,DSAppetite,DSSleeping,DSAgitation,DSThought,DSDetails,DSMovements,DSFatigue,DSWorthlessness,DSConcentration,DSDecisions,DSSuicide,DSPHQ9Score
        //AllergyAnaphylaxis,AllergyCannotUseEpiPen,AllergyFood,AllergyVenom,AllergyInhalants,AllergyMedications,AllergyOther,AllergyHasEpi,AllergyNoEpi,AllergyCanUseEpi
        //FallRiskNumberofFalls,FallRiskSafeHabits,FallRiskDiffProblems,FallRiskGait,FallRiskConcerned,FallRiskFellLastYear,FallRiskFellLastMonth,FallRiskFellInjury,
        //Allergy1,Allergy2,Allergy3,Allergy4,Allergy5
        //MedsNoMeds,MedsDiabetes,MedsSleep,MedsDepression,MedsPain,MedsHeart,MedsBP,MedsThyroid,MedsCholesterol,MedsReflux,MedsAntiSeizure,MedsThinner,MedsRespiratory,MedsAllergy,MedsAntibiotics,MedsAsprin,MedsCorticosteroid,MedsAllergyMeds,MedsCalcium,MedsVitamins,MedsOther1,MedsOther2,MedsOther3,MedsOther4,MedsOther5,Medications1,Medications2,Medications3,Medications4,Medications5,Medications6,Medications7,Medications8,Medications9,Medications10,Medications11,Medications12,Medications13,Medications14,Medications15,Medications16,Medications17,Medications18,Medications19,Medications20
        //Vaccine1,Vaccine2,Vaccine3,Vaccine4,Vaccine5,Vaccine6
        //ADLBathing,ADLInhalerusage,ADLFeedingself,ADLInOutCar,ADLDressing,ADLInOutBed,ADLPutOnTakeOffClothes,ADLInOutTub,ADLDryPowderInhaler,ADLHousekeeping,ADLDifficultyManaging,ADLUseOfPhone,ADLAdministerMeds,ADLManageMoney,ADLShopping,ADLUsesPillOrganizer
        //AdvDirWantsSpeakPhys,AdvDirHasSpokenPhys,AdvDirAdvancedDirectives
        //ObsHasSeenProv2Mo,ObsHasNotSeenProv2Mo,ObsTakingMedsAsRx,ObsNotTakingMedsAsRx,ObsPtComplaintsNew,ObsPtComplaintsNotNew,ObsPtDiscussedDepression,ObsPtNotDiscussedDepression,ObsPtDiscussedOutlook,ObsPtNotDiscussedOutlook
        //RVRFDiabetes,RVRFCHD,RVRFStroke,RVRFColorectal,RVRFOsteoporosis,RVRFDepression,RVRFCognitive,RVRFFunctional,RVRFProstate,RVRFCOPD

        $entry['insurance_plans']['primary'] = [
            "plan" => "Medicare",
        ];

        //PrimaryIns,PrimaryInsPol,SecondaryIns,SecondaryInsPol
//        $entry['insurance_plans']['primary']   = [
//            "plan"           => "Test Insurance",
//            "group_number"   => "",
//            "policy_number"  => "TEST1234",
//            "insurance_type" => "Medicaid",
//        ];
//        $entry['insurance_plans']['secondary'] = [
//            "plan"           => "Test Medicare",
//            "group_number"   => "",
//            "policy_number"  => "123455",
//            "insurance_type" => "Medicare",
//        ];

        $entry['problems'][] = [
            "name" => $row['description'],
        ];

//        $entry['problems'][]    = [
//            "name"       => "Chronic Obstructive Pulmonary Disease",
//            "code_type"  => "ICD9",
//            "code"       => "496",
//            "start_date" => "07-30-2013",
//        ];

//        $entry['medications'][] = [
//            "name"       => "Avinza 30 mg oral capsule, ER multiphase 24 hr",
//            "sig"        => "take 1 capsule by oral route daily for 30 days",
//            "start_date" => "2014-03-11",
//        ];

//        $entry['allergies'][] = [
//            "name" => "Animal Dander",
//        ];

        $this->patients[$patientId] = $entry;
    }

    private function getProviderValue($row, $default = null) {
        $firstName  = $this->getValue($row, 'providerfirstname', '');
        $middleName = $this->getValue($row, 'providermiddlename', '');
        $lastName   = $this->getValue($row, 'providerlastname', '');
        $result = '';
        if ( ! empty($firstName)) {
            $result .= $firstName;
            if ( ! empty($middleName)) {
                $result .= ' ';
            }
        }
        if ( ! empty($middleName)) {
            $result .= $middleName;
            if ( ! empty($lastName)) {
                $result .= ' ';
            }
        }
        if ( ! empty($lastName)) {
            $result .= $lastName;
        }

        if (empty($result)) {
            $result = $default;
        }
        return $result;
    }

    private function getValue($row, $key, $default = null)
    {
        if ( ! isset($row[$key])) {
            return $default;
        }

        if (empty($row[$key])) {
            return $default;
        }

        if ($row[$key] === "null" || $row[$key] === "NULL" || $row[$key] === "\\N") {
            return $default;
        }
        return $row[$key];
    }


}