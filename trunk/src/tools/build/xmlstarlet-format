#!/bin/bash

usage() {
  echo "USAGE:    ./${0##*/} XMLFILE"
  echo "EXAMPLES: ./${0##*/} phpunit.xml"
}

FILE="${1}"
if [ -f "${FILE}" ]; then
  if [ "x$(/usr/bin/file --mime-type --brief ${FILE})" = "xapplication/xml" ]; then 
    /usr/bin/xmlstarlet sel -B -t -c "/" "${FILE}" | \
      /usr/bin/xmlstarlet c14n --without-comments - | \
      /usr/bin/xmlstarlet fo -s 2
  else
    usage; exit 2;
  fi
else 
  usage; exit 1;
fi
