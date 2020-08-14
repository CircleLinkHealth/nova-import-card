# CCM Billing Module
This module handles everything related to billing customers for CCM services.

# Assumptions
You are familiarized with Approve Billable Patients page (ABP).

# High Level Description of Business Logic
At the beginning of every month we attach "unfulfilled" chargeable services to each patient, depending on which chargeable services are enabled for the patient's location, and the patient's conditions. Every time an admin is loading patients using the ABP, we will re-valuate each patient's chargeable services. We will do the same at the end of the month.

# Important Quality Conditions

