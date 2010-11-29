#!/bin/sh

OLDPWD=$(pwd)
cd $(dirname "$0")
php ./runner.php --verbose $@
cd $OLDPWD
