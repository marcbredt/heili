#!/bin/bash
# simple deploy script for this project


### debug ###
set +x 


### variables ###

PARAMS="${@}"
RUN_PARAMS="${PARAMS/*--run/}"
if [ "${PARAMS}" = "${RUN_PARAMS}" ]; then
  RUN_PARAMS=""
  ADD_PARAMS="${PARAMS[@]}"
else 
  RUN_PARAMS="${RUN_PARAMS/ /}"
  RUN_PARAMS="${RUN_PARAMS%% *}"
  ADD_PARAMS="${PARAMS%%--run *} ${PARAMS##*${RUN_PARAMS}}"
  ADD_PARAMS="${ADD_PARAMS/ /}"
fi

SYS_PROD="${ADD_PARAMS%% *}"
SYS_OWNER="www-data"
SYS_GROUP="www-data"

DIR_LOG="../../log"
DIR_DOC="../../doc"
DIR_DEPLOY="../../deploy"
DIR_SOURCE="../../source"

GPG_DEFAULT_KEY="DB90894C"
# watch out for path quotations if the file name contains special characters
# like '~' which represents the user's home directory but will not be resolved
# upon command invocation
GPG_DEFAULT_PASSPHRASE_FILE="/home/marc/.gnupg/passphrase" 


### usage ###
usage(){
  echo "USAGE:    ./${0##*/} --run OPS [PRODUCTIVE]"
  echo "          ./${0##*/} [PDODUCTIVE] --run OPS"
  echo "                     where OPS is a comma separated list containing a "
  echo "                     combination of 'clean','tests','sign','doc',"
  echo "                     'deploy' which will invoke the corresponding "
  echo "                     action."
  echo "                     The PRODUCTIVE flag allows to reduce the deployed "
  echo "                     files which are not necessary in a productive"
  echo "                     environment like example, phpunit or doxygen files."
  echo "EXAMPLES: ./${0##*/} --run clean,tests,sign,doc,deploy"
  echo "          ./${0##*/} y --run clean,sign,deploy"
  echo "          ./${0##*/} --run clean,sign,deploy y"
}


### clean ###
clean() {
  if [[ "${RUN_PARAMS}" =~ clean ]]; then
    echo "I: Cleaning ..."
    bins/clean.sh || exit 1
    echo
  fi
}


### tests ###
tests() {
  if [[ "${RUN_PARAMS}" =~ tests ]]; then
    echo "I: Running tests ..."
    bins/test.sh || exit 1
    echo
  fi
}


### sign ###
# signing modules
sign() {
  if [[ "${RUN_PARAMS}" =~ sign ]]; then
    echo "I: Signing modules ..."
    if [ "$(id -nu)" = "root" ]; then 
      lu=$(stat -c %U bins/modularize.sh)
      echo "I: Running 'bins/modularize.sh' as invoked user '${lu}' ..."
      su -c "bins/modularize.sh ${GPG_DEFAULT_KEY} ${GPG_DEFAULT_PASSPHRASE_FILE} \\
              ${SYS_PROD}" ${lu} || exit 1
    else
      # watch out for phrase file quotations in case of special characters (~)
      echo "I: Running 'bins/modularize.sh' as current user '$(id -nu)' ..."
      bins/modularize.sh ${GPG_DEFAULT_KEY} ${GPG_DEFAULT_PASSPHRASE_FILE} \
        ${SYS_PROD} || exit 1
    fi
    echo
  fi
}


### doc ###
# build documentation
doc() {
  if [[ "${RUN_PARAMS}" =~ doc ]]; then
    echo "I: Building documentation ..."
    bins/document.sh 
    echo
  fi
}


### deploy ###
deploy() {
  if [[ "${RUN_PARAMS}" =~ deploy ]]; then
    echo "I: Deploying ..."
    mkdir -p ${DIR_LOG} ${DIR_DEPLOY}
    find -maxdepth 1 -and -type d -and -not -wholename "./test" \
                     -and -not -wholename "." -and -not -wholename "./bins" \
                     -and -not -wholename "./ptch" -and -not -wholename "./dump" \
                     -exec cp -Rf {} ${DIR_DEPLOY} \;
    if [ "x${SYS_PROD}" = "xy" ]; then 
      rm -rf ${DIR_DEPLOY}/conf/doxygen;
      rm -rf ${DIR_DEPLOY}/conf/phpunit;
      rm -rf ${DIR_DEPLOY}/main/examples; 
    fi
    ln -s trunk/src ${DIR_SOURCE}
    if [ "$(id -nu)" = "root" ]; then 
      lu=$(stat -c %U bins/deploy.sh)
      echo "I: Changing permissions on '${DIR_DEPLOY}', '${DIR_DOC}' and '${DIR_LOG}' ..."
      find ${DIR_DEPLOY} -type d -exec chmod 770 {} +
      find ${DIR_DEPLOY} -type f -exec chmod 660 {} +
      find ${DIR_DEPLOY} -exec chown ${SYS_OWNER}:${SYS_GROUP} {} +
      find ${DIR_LOG} -type d -exec chmod 770 {} +
      find ${DIR_LOG} -exec chown ${SYS_OWNER}:${SYS_GROUP} {} +
      find ${DIR_DOC} -type d -exec chmod 770 {} + 2>/dev/null
      find ${DIR_DOC} -exec chown ${lu}:${lu} {} + 2>/dev/null
      chown ${lu}:${lu} ${DIR_SOURCE}
    else
      echo -n "W: You need be root to change permissions on '${DIR_DEPLOY}', "
      echo    "'${DIR_DOC}' and '${DIR_LOG}'"
    fi
  fi
}


### main ###
cd ${0%/*}/..
if [[ "${@}" =~ --usage ]]; then usage; exit 0; fi
for action in $(echo ${RUN_PARAMS} | tr ',' ' '); do $action; done
cd - >/dev/null
exit 0
