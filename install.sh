#!/bin/bash
##--------------------------------------------------------
 # Module Name : ApplianceManager
 # Version : 1.0.0
 #
 # Software Name : OpenServicesAccess
 # Version : 1.0
 #
 # Copyright (c) 2011 – 2014 Orange
 # This software is distributed under the Apache 2 license
 # <http://www.apache.org/licenses/LICENSE-2.0.html>
 #
 #--------------------------------------------------------
 # File Name   : ApplianceManager/RunTimeAppliance/install.sh
 #
 # Created     : 2013-03
 # Authors     : Benoit HERARD <benoit.herard(at)orange.com>
 #
 # Description :
 #      Basic installation
 #--------------------------------------------------------
 # History     :
 # 1.0.0 - 2013-03-14 : Release of the file
##
COMPILE_MODULE=0
INSTALL_DIR=""
USAGE=0
for p in $* ; do
	if [ "$p" == "-m" ] ; then
		COMPILE_MODULE=1
	elif [ "$p" == "-h" ] ; then
		USAGE=1
	else
		INSTALL_DIR=$p
	fi
done


if [ "$INSTALL_DIR" == "" -o $USAGE -eq 1 ] ; then
	echo `basename $0` [-m] INSTALLATION-DIR
	echo "\t-m also compile and install apache module (assume that: c compiler, make, autoconf, apache-dev tools and headers, mysqlclient-dev header are available)"
	exit 1
fi
mkdir -p $INSTALL_DIR
[ -f $INSTALL_DIR/ApplianceManager.php/include/Crypto.ini.php ] && cp $INSTALL_DIR/ApplianceManager.php/include/Crypto.ini.php $INSTALL_DIR/Crypto.ini.php.sav
[ -f $INSTALL_DIR/RunTimeAppliance/shell/envvars.sh ] && cp $INSTALL_DIR/RunTimeAppliance/shell/envvars.sh $INSTALL_DIR/RunTimeAppliance/shell/envvars.sh.sav && rm -rf $INSTALL_DIR/ApplianceManager.php
cp -R ApplianceManager.php $INSTALL_DIR
cp -R RunTimeAppliance $INSTALL_DIR
cp -R sql $INSTALL_DIR
[ -f $INSTALL_DIR/Crypto.ini.php.sav ] && mv $INSTALL_DIR/Crypto.ini.php.sav $INSTALL_DIR/ApplianceManager.php/include/Crypto.ini.php
[ -f $INSTALL_DIR/RunTimeAppliance/shell/envvars.sh.sav ] && mv $INSTALL_DIR/RunTimeAppliance/shell/envvars.sh.sav $INSTALL_DIR/RunTimeAppliance/shell/envvars.sh



if [ $COMPILE_MODULE -eq 1 ] ; then
	cd $INSTALL_DIR/RunTimeAppliance/apache/module
	aclocal
	autoconf
	automake
	./configure
	make
	make install
	a2enmod osa
fi


cat $INSTALL_DIR/RunTimeAppliance/shell/envvars.sh| sed "s|INSTALL_DIR=.*|INSTALL_DIR=$INSTALL_DIR|g" > $$.tmp
cat $$.tmp >$INSTALL_DIR/RunTimeAppliance/shell/envvars.sh
rm $$.tmp

echo "Done!"
if [ !  -f  /etc/apache2/mods-enabled/osa.load ] ; then
	echo ""
	echo " **** IMPORTANT NOTE *********"
	echo "mod_osa apache is not installed, first install it, then"
fi

echo "You can now go to $INSTALL_DIR/RunTimeAppliance/shell, edit envvars.sh file, check vars (Basic configuration section at minimum) and run configure-osa.sh"
echo "	cd $INSTALL_DIR/RunTimeAppliance/shell"
echo "	vi envvars.sh"
echo "	./configure-osa.sh"
echo ""
echo "If, at the end of execution the message "OSA Configuration done, exiting..." appears, OSA is correctly installed, configured and running!"

	

