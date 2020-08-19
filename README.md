# CCM Billing Module
This module handles everything related to billing **Customers** for CCM services provided to **Patients**.

# Assumptions
You are familiarized with Approve Billable Patients page (ABP).

# Key Concepts
- A **Customer** is someone whom we service **Patients** for specific Chargeable Services (CPT Codes).
  Examples of Customer are Locations, and Practices.
- A **Processor** is a class responsible for processing billing and giving us data. There are 2 kinds of processors.
  A Patient processor would process a billing code for a Patient. Each billing code's billing logic is lives in a separate class with the billing code's friendly     name, (eg. CCM, BHI, AWV1, AWV2, PCM, etc)
  A Customer processor would call all Patient processors on each Patient to produce each month's billing for the Customer.

# High Level Description of Business Logic
At the beginning of every month we attach "unfulfilled" chargeable services to each patient, depending on which chargeable services are enabled for the patient's Location, and the patient's conditions. Every time an admin is loading patients using the ABP, we will re-valuate each patient's chargeable services. We will do the same at the end of the month.

# Important Quality Conditions
- If an admin has made a change to a row on ABP, that row should not be altered by CPM, except for the beginning of the following month when CPM creates the dataset that will power ABP.

# V2 Planning Notes
- Enabled chargeable services will be attached to Location, not Practice.
- Any code will be swritten behind an interface so that we can add other types of Customers, eg. "Provider"
- If we want to add a new type of customer, we will need to create a new DB table similar to "location_monthly_chargeable_summaries"
- We will create "patient_monthly_chargeable_summaries" to store chargeable service status per patient, per month, per chargeable service.
