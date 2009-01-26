#!/bin/sh
# ---------------------------------------------------------------------------
# Seagull PHP Framework
# ---------------------------------------------------------------------------
# PostgreSQL create database utility
# 

# usage
if [ $# = 0 ]; then
    echo "Syntax: seagull-pgsql-createDB <PGusername> <databasename> [host]"
    echo "        <PGusername>   : PostgreSQL user name for creating the database"
    echo "        <databasename> : Name of database t be used for Seagull PHP Famework"
    echo "        [host]         : Optional name or address of remote PostgreSQL server,"
    echo "                         if missing connection will made through a socket to local server"
    echo ""
    echo "Please change your pg_hba.conf file to permit your PGusername to connect to"
    echo "PgSQL server using password or md5 method."
    echo "Some example below:"
    echo ""
    echo "# TYPE  DATABASE  USER        CIDR-ADDRESS  METHOD"
    echo "local   all       PGusername                password"
    echo "host    all       PGusername  127.0.0.1/32  md5"
    echo ""
    exit 1
fi

# greeting
echo "Seagull PHP Framework - PostgreSQL database creation utility"
echo "------------------------------------------------------------"
echo "This script will help you in creating user and database ready to be"
echo "used for the Seagull installation procedure"
echo "Press Ctrl+C to abort this script"

# parsing parameters
while getopts ":d" Option
do
    case $Option in
        d ) echo "WARNING: option -d selected: user $1 and table $2 will be dropped before creating them"
	    echo "Are you sure [y/N]? (Ctrl+C to abort)"
	    read DROPANSW
	;;
    esac
done
shift $(($OPTIND - 1))

# setting up host parameter
if [ "X$3" = "X" ]; then
    PGHOST=
else
    PGHOST="-h $3"
fi

# drop user and database if requested
if [ "$DROPANSW" = "y" -o "$DROPANSW" = "Y" ]; then
    dropdb $PGHOST --username postgres $2
    if [ $? -ne 0 ]; then
        echo "ERROR in dropping database $2: maybe your pg_hba.conf file is not"
        echo "configured to let user 'postgres' to connect to PostgreSLQL server"
        echo "using a password from local or from host $3"
    fi
    dropuser $PGHOST --username postgres $1
    if [ $? -ne 0 ]; then
        echo "ERROR in dropping database $2: maybe your pg_hba.conf file is not"
        echo "configured to let user 'postgres' to connect to PostgreSLQL server"
        echo "using a password from local or from host $3"
        exit 1
    fi
fi


# creating user
echo ""
echo "Step 1: Creating user $1"
createuser $PGHOST --username postgres --createdb --no-adduser --pwprompt $1 
if [ $? -ne 0 ]; then
    echo "ERROR in creating user $1: maybe your pg_hba.conf file is not"
    echo "configured to let user 'postgres' to connect to PostgreSLQL server"
    echo "using a password from local or from host $3"
    exit 1
fi

# database creation
# NOTE: change the encodign if latin1 don't fit your needs
echo ""
echo "Step 2: creating database $2"
createdb $PGHOST --encoding 'latin1' --username $1 $2
if [ $? -ne 0 ]; then
    echo "ERROR in creating database $2: maybe your pg_hba.conf file is not"
    echo "configured to let user '$1' to connect to PostgreSLQL server"
    echo "using a password from local or from host $3"
    exit 1
fi

# adding plpgsql language to out database
echo ""
echo "Step 3: adding plpgsql language"
createlang $PGHOST --username postgres plpgsql $2 
if [ $? -ne 0 ]; then
    echo "ERROR in adding language plpgsql to database $2"
    exit 1
fi
