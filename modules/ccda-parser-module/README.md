# Summary
This module provides a ccd:parse that can be used to parse an xml to json.
Example: `php artisan ccd:parse 100 ./path/to/xml`

# Installation
In composer.json add
```
"repositories": [
    {
      "type": "git",
      "url": "git@github.com:CircleLinkHealth/ccda-parser-processor-php.git"
    }
  ],
 "require": {
      "circlelinkhealth/ccda-parser-processor-php": "dev-master"
 }
```

Run `composer install && npm install`

Add below .env variables:

```
CCDA_PARSER_STORE_RESULTS_IN_DB=true
CCDA_PARSER_DB_CONNECTION=mysql
CCDA_PARSER_DB_JSON_TABLE=ccdas
```

# Usage
`php artisan ccd:parse 100 ./path/to/xml`

## Notes
- If CCDA_PARSER_STORE_RESULTS_IN_DB is false, the command takes a third argument for the output file path: php artisan ccd:parse 100 ./path/to/xml ./path/to/output.json
- The CCDA_PARSER_DB_JSON_TABLE is auto created if it does not exist.


# Node.js Notes
- export INPUT_FILE=./path-to-ccd
- export OUTPUT_FILE=./path-to-save-json-file
- node index $INPUT_FILE $OUTPUT_FILE

## Note
- In case of error, process exit code will != 0 and it will be returned on **stderr**
- Otherwise, process exit code will be 0 and file will be found in **$OUTPUT_FILE**
 
## Related CPM PR
https://github.com/CircleLinkHealth/app-cpm-web/pull/3364
