#!/bin/sh

for ff in `ls` 
do 
   if [ -d $ff/data ] 
   then
      cd $ff/data
      [ -f schema.oci.drop.sql ] && sqlplus test/comverse @ schema.oci.drop.sql
      cd ../..
   fi
done
