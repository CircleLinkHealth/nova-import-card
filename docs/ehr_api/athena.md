#AthenaHealth
Athenahealth Inc. is a developer of cloud-based practice management and electronic health record (EHR) systems for small to medium-sized physician practices and hospitals.

##Reading tips:
Athena can't actually provide us the patient id's directly that's why we collect patient id's by retrieving past appointments.


##Connection
The connection is established when an object of the class Connection.php is constructed.

**Please review the [Connection.php](App\Services\AthenaAPI\Connection.php) file** to learn more on how the connection is established. (Documentation provided by AthenaHealth)

##Calls
We collect the information we need (e.g. to determine if a patient is eligible for enrollment for our services) by making certain calls(requests) to the Athena API. 

- `GetBookedAppointments()` => gets a practice's booked appointments for a specific date range
- `GetPatientProblems()` => gets given patient problems (for later use in determining eligibility). Requires patient, practice and department ids.
- `GetPatientInsurances()` => gets given patient insurances (for later use in determining eligibility). Requires patient, practice and department ids.
- `CreateNewPatient()` => creates a new patient in the athena testing server. This is essential for testing.


##DetermineEnrollmentEligibility
This collects important data about a given patient in the athena database, provides services that use the existing calls in calls.php to retrieve information and determine the eligibility for each patient.

