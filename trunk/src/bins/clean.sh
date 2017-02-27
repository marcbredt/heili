#!/bin/bash
# simple clean script for this project

cd ${0%/*}/..

rm -rf ../../log ../../deploy ../../doc #../../source/main/css/*.css
rm -f ../../source

cd - >/dev/null
