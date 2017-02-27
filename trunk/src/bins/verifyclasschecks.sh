#!/bin/bash
#
# This is a helper to verify internal class checks. 
# Bases on the convention classes are namespaced through their location.
#
# NOTE: currently it needs to be a convention to avoid newlines in validator
#       class checks to be still able to grab the target class correctly
#       otherwise an abstract class name will be generated which makes the
#       the build process fail with, e.g.
#         "Searching for class if(!Validator::isclass($seg->get_shm_seg_sem_read(), 
#            ... [ missing ]"
#         "Searching for class } ... [ missing ]"
#         "Searching for class 254: ... [ missing ]"
#

cd ${0%/*}/..

# list to exclude checks for specific classes, e.g. for those provided through
# any php package
declare -a EXCLUDES=("DOMAttr" "DOMComment" "DOMDocument" "DOMElement" 
                     "DOMNodeList" "DOMText" "DOMCdataSection" "Exception")

OLD_IFS=${IFS}
IFS=$'\n'
failed="n"
classestocheck=""

# collect classes first to sort and and uniq them
for file in $(find -type f -name "*\.php"); do

  # first get newline splitted class string checks for each source file
  tmp="$(grep -rn "Validator::isclass(" "${file}" | \
         sed -re 's/.*Validator::isclass\(.*,(.*)\).*/\1/g' | \
         cut -d'"' -f2 | grep -v "^\\$")"
  # replacing whitespaces with newlines as class string does not contain some
  tmp="$(echo ${tmp} | sed -e 's#\\#/#g' -e 's/ /\\n/g')"

  # concatenate sourcefile strings, newline should erepresented via \\n now
  # for further processing
  if [ "x${classestocheck}" = "x" ]; then
    classestocheck="${tmp}"
  else
    if [ "x${tmp}" != "x" ]; then 
      classestocheck="${classestocheck}\\n${tmp}"
    fi
  fi

done

# now search for the class files defined by the checks
for class in $(echo -e ${classestocheck} | sort | uniq); do

  path="${class,,}.class.php" 

  echo -n "Searching for class ${class} ... "

  if ! [[ "${EXCLUDES[@]}" =~ ${class} ]] && [ -f "${path//\\//}" ]; then
    echo -e "[ \033[0;32mfound\033[0m ]"
  elif [[ "${EXCLUDES[@]}" =~ ${class} ]]; then
    echo "[ skipped ]"
    continue
  else
    echo -e "[ \033[10;31mmissing\033[0m ]"
    failed="y"
  fi
  
done

IFS=${OLD_IFS}

# indicate errors for ant if a classfile was not found but however complete all
# the checks on classes going to be checked internally
if [ "x${failed}" = "xy" ]; then exit 1; fi

cd - >/dev/null
