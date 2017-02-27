#!/bin/bash
#
# helper to create necessary module files
#   especially for creating signature files
#

set +x

usage() {
  echo "USAGE:    ./${0##*/} KEY      PASSPHRASEFILE [PRODUCTIVE]"
  echo "EXAMPLES: ./${0##*/} DB90894C passphrase"
  echo "          ./${0##*/} DB90894C passphrase y"
}

GPG_KEY=${1}
GPG_PASSPHRASE_FILE=${2}
SYS_PROD=${3}

LICFILE="../LICENSE"
LICMODFILE="conf/license.xml"
SIGMODFILE="conf/signature.xml"

if [ "${#}" != "2" -a "${#}" != "3" ]; then usage; exit 1; fi

cd ${0%/*}/..


### CORE BUILD ###

# build core module files, license/signature
echo "I: Building module files for module 'core' ..."
cat <<LICEND > ./${LICMODFILE}
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<license type="bsd">
$(cat ${LICFILE})
</license>
LICEND
# exclude example, doxygen, phpunit files when deploying productive
if [ "x${SYS_PROD}" = "xy" ]; then
fhash=$(find -mindepth 1 -maxdepth 1 -type d \
             -not -wholename "./mods" -and -not -wholename "./test" \
             -and -not -wholename "./ptch" -and -not -wholename "./dump" \
             -and -not -wholename "./bins" \
             -exec find {} -type f -not -wholename "./${SIGMODFILE}" \
                           -and -not -wholename "./conf/doxygen/*" \
                           -and -not -wholename "./conf/phpunit/*" \
                           -and -not -wholename "./main/examples/*" \; | \
        awk '{ system("md5sum "$1); }' | \
        cut -d' ' -f1 | md5sum | cut -d' ' -f1)
# include example, doxygen, phpunit files otherwise
else
fhash=$(find -mindepth 1 -maxdepth 1 -type d \
             -not -wholename "./mods" -and -not -wholename "./test" \
             -and -not -wholename "./ptch" -and -not -wholename "./dump" \
             -and -not -wholename "./bins" \
             -exec find {} -type f -not -wholename "./${SIGMODFILE}" \; | \
        awk '{ system("md5sum "$1); }' | \
        cut -d' ' -f1 | md5sum | cut -d' ' -f1)
fi
echo "I: Signing files hash '${fhash}' for module 'core' ..."
cat <<SIGCOREEND > ./${SIGMODFILE}
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<signature type="pgp-armor" key="${GPG_KEY}">
$(echo ${fhash} | gpg --homedir /home/marc/.gnupg --no-tty --sign-with ${GPG_KEY} \
    --passphrase-file "${GPG_PASSPHRASE_FILE}" --detach-sign --armor - 2>/dev/null)
</signature>
SIGCOREEND


### MODULES BUILD ###
# build all module files,
for m in $(find ./mods/ -maxdepth 1 -mindepth 1); do 
echo "I: Building module files for module '${m##*/}' ..."
cat <<LICEND > ${m}/${LICMODFILE} || exit
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<license type="bsd">
$(cat ${LICFILE})
</license>
LICEND
mfhash=$(find ${m} -type f -not -wholename "${m}/${SIGMODFILE}" \
                   -exec md5sum {} + | \
         cut -d' ' -f1 | md5sum | cut -d' ' -f1)
echo "I: Signing module files hash '${mfhash}' for module '${m}' ..."
cat <<SIGMODEND > ${m}/${SIGMODFILE} || exit
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<signature type="pgp-armor" key="${GPG_KEY}">
$(echo ${mfhash} | gpg --homedir /home/marc/.gnupg --no-tty \
    --sign-with ${GPG_KEY} --passphrase-file "${GPG_PASSPHRASE_FILE}" \
    --detach-sign --armor - 2>/dev/null)
</signature>
SIGMODEND
done

cd - >/dev/null
