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
         


## PracticeInvoiceController

#####-makeInvoices()
This method is called when a POST request from `cpm-web/resources/views/billing/practice/create.blade.php` is submitted.

- If the value for the key `format` is `pdf` in the Request, then it calls an instance of the class `PracticeReportsService` and its method `getPdfInvoiceAndPatientReport` which creates a pdf file for [invoice], another for [patient report]and returns an array[]. It then returns the `billing.practice.list` view with the array passed inside the view.
- If the value for the key `format` is `csv` or `xls` in the Request, then it calls an instance of the class `PracticeReportsService` and its method `getQuickbooksReport` which creates an Excel sheet[] and returns an array[] with information needed to download the Excel sheet.




###PracticeReportsService

#####getPdfInvoiceAndPatientReport()

#####getQuickbooksReport()

#####makeQuickbookReport()

#####makeRow()



###PracticeInvoiceGenerator

#####generatePdf()

#####makeInvoicePdf()

#####makePatientReportPdf()

#####getInvoiceData()