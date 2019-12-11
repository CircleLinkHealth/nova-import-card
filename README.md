# Usage

- export INPUT_FILE=./path-to-ccd
- export OUTPUT_FILE=./path-to-save-json-file
- node index $INPUT_FILE $OUTPUT_FILE

# Note
- In case of error, process exit code will != 0 and it will be returned on **stderr**
- Otherwise, process exit code will be 0 and file will be found in **$OUTPUT_FILE**
 
