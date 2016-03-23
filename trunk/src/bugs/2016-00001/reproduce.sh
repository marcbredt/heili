#!/bin/bash

path=${0%/*}
file=${0##*/}
logf="${path}/${file%\.*}.log"

mjbs=${1}
mcns=${2}

if ! [[ "${mjbs}" =~ ^[0-9]+$ ]]; then
  mjbs=3 # max jobs
         # >2 => sem_aquire failures 
         # <3 => sem_release failures
fi
if ! [[ "${mcns}" =~ ^[0-9]+$ ]]; then
  mcns=1 # max connections per job
fi

echo > "${logf}"
for((i=0;$i<${mjbs};i=$(($i+1)))); do 
  ${0%/*}/connect.sh ${i} ${mcns} "${logf}" & 
  echo "[${!}] ${0%/*}/connect.sh ${i} ${mcns} ${logf}"
done
