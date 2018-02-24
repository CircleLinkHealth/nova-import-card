#AthenaHealth
Athenahealth Inc. is a developer of cloud-based practice management and electronic health record (EHR) systems for small to medium-sized physician practices and hospitals.

##Reading tips:
Athena can't actually provide us the patient IDs we need directly that's why we collect patient IDs by retrieving past appointments.

##Value objects
- `Patient` => Contains the main attributes of a Patient in AthenaAPI. Mainly used to create a Patient for testing. Contains:
    - `$practiceId`
    - `$departmentId`
    - `$dob`
    - `$doNotCall`
    - `$firstName`
    - `$lastName`
    - `$address1`
    - `$address2`
    - `$city`
    - `$email`
    - `$gender`
    - `$homePhone`
    - `$mobilePhone`
    - `$state`
    - `$zip` 

- `Problem` => describes a Problem in AthenaAPI. Mainly used to create a Problem for testing. Contains: 
    - `$practiceId` => The athenaNet practice id.
    - `$patientId` => The athenaNet patient id.
    - `$departmentId` => The athenaNet department id.
    - `$laterality` => Update the laterality of this problem. Can be null, LEFT, RIGHT, or BILATERAL.
    - `$note` => The note to be attached to this problem.
    - `$snomedCode` => The SNOMED code of the problem you are adding.
    - `$startDate` => The onset date to be updated for this problem in MM/DD/YYYY format.
    - `$status` => Whether the problem is chronic or acute.
    
- `ProblemsAndInsurances` => contains the `$problems` and `$insurances` of a Patient. This is used by the `DetermineEnrollmentEligibility` class.

##Adaptors


##TargetPatient Model
The TargetPatient Model is a type of User. When patient IDs are retrieved from the appointments, for each `patient_id` a new instance of the TargetPatient Model is created, and the data[ids] are stored, with a status value `"to process"`. 
Then an eligibility check is run[] to determine if a TargetPatient is eligible for our cpm services, and changes the status accordingly to 'eligible', 'ineligible' or 'error'[if data missing].
If a target Patient is eligible then demographics are collected, in order for this person's details to be inserted in the enrollees table.

- `App\TargetPatient` belongsTo `App\User`




##Enrollee Model
-TargetPatients retrieved from Athena that are eligible for cpm services, trigger the creation of a new instance of the Enrollee Model.
Then the according data are passed to the enrollees table [more on how, provider id? practice our id etc].

##Connection
The authenticated connection is established when an object of the class Connection.php is constructed.

**Please review [Connection.php](App\Services\AthenaAPI\Connection.php)** to learn more on how the connection is established. (Documentation provided by AthenaHealth)

##Calls
We collect the information we need (e.g. to determine if a patient is eligible for enrollment for our services) by making certain calls(requests) to the Athena API. 

- `getBookedAppointments()` => gets a practice's booked appointments for a specific date range. This is used to collect the patient IDs needed for various purposes.
- `getPatientProblems()` => gets a patient problems (for later use in determining eligibility). Requires patient, practice and department ids.
- `getPatientInsurances()` => gets a patient insurances (for later use in determining eligibility). Requires patient, practice and department ids.
- `getDemographics()` => gets a patient demographics, needed to store an eligible TargetPatient in the Enrollees table.
- `getPatientPrimaryProvider()` => gets first and last name of a patient's Primary Provider.[needs work, Athena only gives us Primary Provider id]
- `getAvailablePractices()` => 
- `getDepartmentIds()` => 
- `getNextPage()` => 
- `getPatientCustomFields()` => 
- `getPracticeCustomFields()` => 
- `postPatientDocument()` => 
- `getCcd()` => 

####For Testing
- `createNewPatient()` => creates a new Patient in the athena testing server, and returns a `patient_id`. Expects a `Patient` value object.
- `addProblem()` => adds a problem in the athena testing server for a specific Patient. Expects a `Problem` value object. 



##DetermineEnrollmentEligibility
This class collects patient ids from medical appointments and then proceeds to retrieve the data needed to check each patient's eligibility eligibility for cpm services (problems and insurances).
- `GetPatientIdFromAppointments()` => gets past booked appointments, and collects patient and department IDs for each appointment. It then proceeds to Update or Create a new instance of the TargetPatient model using said values and sets status to `'to_process'`. 
- `GetPatientProblemsAndInsurances()` => gets a Patient's problem and insurances and then proceeds to create a new instance of the value object `ProblemsAndInsurances`.

##CreateAndPostPdfCareplan

- `getAppointments()` => Gets all appointments for a practice for a certain period of time.
- `logPatientIdsFromAppointments()` => 
- `getCcdsFromRequestQueue()` => 
- `postPatientDocument()` => 



##Commands

- `DetermineTargetPatientEligibility()` => 
- `GetAppointments()` => 
- `GetCcds()` => 
- `GetPatientIdFromAppointments()` => 

