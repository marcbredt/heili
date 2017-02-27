#!/bin/bash

# debug
set +x

# usage description
usage() {
  echo "USAGE:    ./${0##*/} OPERATION PARAMS"
  echo "          ./${0##*/} -u"
  echo "          ./${0##*/} -c"
  echo "          ./${0##*/} -a		PFILE"
  echo 
  echo "EXAMPLES: ./${0##*/} -u"
  echo "          ./${0##*/} -c"
  echo "          ./${0##*/} -a		x.patch"
}

# some failure detection
OP=${1}
if ! [[ "${OP}" =~ ^-(c|a|u)$ ]]; then 
  usage; exit 1;
elif [ "${OP}" = "-c" ] && [ "${#}" != "1" ]; then 
  usage; exit 2;
elif [ "${OP}" = "-a" ] && [ "${#}" != "2" ]; then 
  usage; exit 3;
fi

# vars needed
FROMDIR="../../source"
TODIR="../../deploy"
PATCH=

# helper vars
cd ${0%/*}/..

# create a patch
if [ "${OP}" = "-c" ]; then 
  PATCH="ptch/$(date +%Y%m%d%H%M%S).patch"

  if [ ! -d ${FROMDIR} ]; then  
    usage; exit 8;
  elif [ ! -d ${TODIR} ]; then  
    usage; exit 9;
  fi

  # create a patch
  diff -x "*\.patch" -x "\.*\.swp" -x "*\.sql" -x "*\.log" \
       -x "deploy\.sh" -x "patch\.sh" -x "modularize\.sh" -x "clean\.sh" \
       -x "document\.sh" -x "test\.sh" -x "verifyclasschecks.sh" \
       -x "namespace\.sh" \
       -x "xmlstarletformat.sh" -x "taillog.sh" \
       -x "README" -x "NOTES" -x "LICENSE" \
       -x "*phpunit*" -x "*test*" \
       -Naur ${FROMDIR} ${TODIR} > ${PATCH}

  exit 0

# apply a patch
elif [ "${OP}" = "-a" ]; then
  PATCH="$(readlink -f "ptch/${2##*/}")"
  PATCH="ptch/${PATCH##*/}"

  if [ ! -f ${PATCH} ]; then 
    usage; exit 4;
  elif [ "$(file --brief --mime-type ${PATCH})" != "text/x-diff" ]; then
    usage; exit 5;
  fi

  # lvl can be static as FROMDIR and TODIR are always the same for this project
  if patch --verbose --dry-run -p3 < ${PATCH}; then
     echo
     echo "Applying patch after dry run ..."
     patch --verbose -p3 < ${PATCH}
     rm -f main/css/*.css
     #rm ${PATCH}
  fi

  exit 0

# print usage
elif [ "${OP}" = "-u" ]; then
  usage; exit 0;

fi

cd - >/dev/null

