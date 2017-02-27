#!/bin/bash
# simple documentation build script

usage() {
  echo "USAGE:    ./${0##*/}"
  echo "EXAMPLES: ./${0##*/}"
}

cd ${0%/*}/..

# vars needed
DOXYGEN_CONF="conf/doxygen/doxygen.conf"
DOXYGEN_PROJECT="heili"
DOXYGEN_OUTDIR="../../doc/doxygen"
DOXYGEN_INPUT="$(find core mods/*/clss -maxdepth 0 | tr '\n' ' ')"

# set doxygen config values
sed -ri 's:(^PROJECT_NAME[ \t]+=[ \t]+)".*":\1"'${DOXYGEN_PROJECT}'":' ${DOXYGEN_CONF}
sed -ri 's:(^OUTPUT_DIRECTORY[ \t]+=[ \t]+)".*":\1"'${DOXYGEN_OUTDIR}'":' ${DOXYGEN_CONF}
sed -ri 's:(^INPUT[ \t]+=[ \t]+)".*":\1'"${DOXYGEN_INPUT}"':' ${DOXYGEN_CONF}

# build documentation
mkdir -p ${DOXYGEN_OUTDIR}
doxygen ${DOXYGEN_CONF}

cd - >/dev/null
