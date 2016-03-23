#!/bin/bash

jbid="${1}"
nums="${2}"
logf="${3}"

tget="http://heili.berlin.lo/examples/shm/shmhandler.php"

if ! [[ "${nums}" =~ ^[0-9]+$ ]]; then nums=3; fi
if ! [ -f "${logf}" ]; then logf="${0%/*}/reproduce.log"; fi

rnum=0
while [ "${rnum}" -lt "${nums}" ]; do 
  echo "Job #${jbid}: Run #${rnum}:" >> "${logf}"
  /usr/bin/curl -v -k "${tget}" &>> "${logf}"
  ((rnum++))
  echo "---" >> "${logf}"
done
