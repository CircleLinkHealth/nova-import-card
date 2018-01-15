# Practice Invoices and Patient Reports

## Tips:
- To make the creation of pdf and excel sheets possible(ghostscript), the files: `wkhtmltopdf` and `wkhtmltopdf-amd64` must be placed in the folder `cpm-web[project directory]/vendor/h4cc/wkhtmltopdf-amd64/bin`, and then run `brew install ghostscript`.

##ChargeableService Model
Represents the services that practices and/or providers are charged.
Its properties are: `code` (e.g. CPT99490), `description` and `amount` (currency).

ChargeableService shares a **Many to Many Polymorhpic Relationship** to the `Practice` and `User`(Provider) models:

    - practices()->morphedByMany(Practice::class, 'chargeable')
    - providers()->morphedByMany(User::class, 'chargeable')
    
Similarly, the inverse of the relationship:

    - App\User->chargeableServices()->morphToMany(ChargeableService::class, 'chargeable')
    - App\Practice->chargeableServices()->morphToMany(ChargeableService::class, 'chargeable')
    
Intermediate table: `chargeables`. 

A ChargeableService can be charged to either a Practice or a Provider, or both.
         
##QuickBooksRow Value Item

##QuickBooksRow Resource

## PracticeInvoiceController

#####-makeInvoices()
This method is called when a POST request from `cpm-web/resources/views/billing/practice/create.blade.php` is submitted.

- If the value for the key `format` is `pdf` in the Request, then it calls an instance of the class `PracticeReportsService` and its method `getPdfInvoiceAndPatientReport` which creates a pdf file for [invoice], another for [patient report]and returns an array[]. It then returns the `billing.practice.list` view with the array passed inside the view.
- If the value for the key `format` is `csv` or `xls` in the Request, then it calls an instance of the class `PracticeReportsService` and its method `getQuickbooksReport` which creates an Excel sheet[] and returns an array[] with information needed to download the Excel sheet.




###PracticeReportsService
Provides the main functions needed by PracticeInvoiceController to either produce PDF or Quickbooks.

#####getPdfInvoiceAndPatientReport()

#####getQuickbooksReport()

#####makeQuickbookReport()

#####makeRow()
Receives an instance of the model Practice which is assigned to the attribute `$practice` and a Carbon item for `$date`.

It then creates a new instance of the class PracticeInvoiceGenerator, passing in $practice and $date, and uses it to `makePatientReportPdf()` (to get file path and create a shortened download link to be placed in the QuickBooksRow in the column `'PT.Billing Report:'`)
and to `getInvoiceData()` to be used in the rest of the QuickBooksRow columns.

Finally it calls on the `chargeableServices()` relationship of the Practice to retrieve the `ChargeableService` code, to be used in the QuickBooksRow column `'Line Item'`.

Returns a complete row [array at the moment soon to be refactored to Value Object], to be used in [].



###PracticeInvoiceGenerator
Receives an instance of the model Practice which is assigned to the attribute `$practice`, and a Carbon item for `$month`.

#####generatePdf()

#####makeInvoicePdf()

#####makePatientReportPdf()

#####getInvoiceData()

Counts all the instances of the User model with role `'participant'`, which also have a relationship with the `PatientMonthlySummary` class in which `'month_year'` matches the $month attribute,
`'ccm_time'` is `>=` than 1200 and `'approved'` equals `true`, and stores them as $billable.
 
 It then uses this value as well as the data contained in the $practice attribute to return an array of values[used where].