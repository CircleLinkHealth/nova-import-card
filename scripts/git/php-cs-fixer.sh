#!/bin/bash
################################################################################
#
# Bash PHP Coding Standards Fixer
#
# This will prevent a commit if the tool has made changes to the files. This
# allows a develop to look at the diff and make changes before doing the
# commit.
#
# Exit 0 if no errors found
# Exit 1 if errors were found
#
# Requires
# - php
#
# Arguments
# - None
#
################################################################################
# Plugin title
title="PHP Code Fixer"

# Possible command names of this tool
local_command="php-cs-fixer.phar"
vendor_command="vendor/bin/php-cs-fixer"
global_command="php-cs-fixer"

# Print a welcome and locate the exec for this tool
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source $DIR/helpers/colors.sh
source $DIR/helpers/formatters.sh
source $DIR/helpers/welcome.sh
source $DIR/helpers/locate.sh

# Build our list of files, and our list of args by testing if the argument is
# a valid path
CHANGED_FILES=$(git diff --name-only --diff-filter=ACM)
args="--verbose --show-progress=dots --diff --path-mode=override"

# Run the command on each file
php_errors_found=false
error_message=""
for path in "${CHANGED_FILES[@]}"
do
    if [[ ${path} == *"resources/views/"* ]]; then
        echo -e "${txtylw} $exec_command fix ${path} --config=.php_cs_views.php ${args} ${txtrst}"

        ${exec_command} fix ${path} --config=.php_cs_views.php ${args} 1> /dev/null
    elif [[ ${path} == *".php"* ]]; then
        echo -e "${txtylw} $exec_command fix ${path} --config=.php_cs.dist  ${args} ${txtrst}"

        ${exec_command} fix ${path} --config=.php_cs.dist  ${args} 1> /dev/null
    fi

    if [ $? -ne 0 ]; then
        error_message+="  - ${txtred}${path}${txtrst}\n"
        php_errors_found=true
    else
        echo -e "git add ${path}"
        git add ${path}
    fi
done;

# There is currently debate about exit codes in php-cs-fixer
# https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/1211
if [ "$php_errors_found" = true ]; then
    echo -en "\n${undred}${title} updated the following files:${txtrst}\n"
    echo -en "\n${error_message}"
    echo -en "\n${txtwht}${bakred}Please review the changes above. If you're happy, please commit and push.${txtrst}\n"
    exit 1
fi

echo "${txtgrn} All good! 😍 You may proceed to committing/pushing your code.${txtrst} \n"
exit 0
