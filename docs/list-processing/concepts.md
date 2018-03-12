# Processing Lists - Concepts

## Reading tips:
This document is essentially an introductory `brain dump` on lists.

## What is a List?
Lists are normally in `*.csv`.
A list can be:
- Patients that we will be processing for eligibility (decide whether or not patients are eligible for CCM).
- Patients that are enrolled, and need to be imported into CPM.

- Lists can contain all the necessary data in the csv, or they can contain a link to a CCDA.
- Look for columns named `medical_record_type` and `medical_record_id`.
    example types: `App\Models\MedicalRecords\Ccda` or `App\Models\MedicalRecords\TabularMedicalRecord`
    
  If no such columns are found, then assume this is a tabular medical record (ie, all the informations is contained in the csv). 