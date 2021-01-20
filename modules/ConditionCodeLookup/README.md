# laravel-condition-code-lookup

#### Lookup ICD-9, ICD-10 and SNOMED codes from the following APIs:
- https://clinicaltables.nlm.nih.gov/apidoc/icd9cm_dx/v3/doc.html (ICD-9)
- http://www.icd9data.com/ (ICD-9)
- https://clinicaltables.nlm.nih.gov/apidoc/icd10cm/v3/doc.html (ICD-10) 

#### Caching
Since codes don't change, it is recommended to enable caching:
- Publish config file
```
php artisan vendor:publish --provider="CircleLinkHealth\ConditionCodeLookup\Providers\ConditionCodeLookupServiceProvider"
```
- Set DB configuration in config file
