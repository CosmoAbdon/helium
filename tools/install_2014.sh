#!/bin/bash
# ////////////////////////////////////////////////////////////////////////////////
# //BOCA Online Contest Administrator
# //    Copyright (C) 2003-2012 by BOCA Development Team (bocasystem@gmail.com)
# //
# //    This program is free software: you can redistribute it and/or modify
# //    it under the terms of the GNU General Public License as published by
# //    the Free Software Foundation, either version 3 of the License, or
# //    (at your option) any later version.
# //
# //    This program is distributed in the hope that it will be useful,
# //    but WITHOUT ANY WARRANTY; without even the implied warranty of
# //    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# //    GNU General Public License for more details.
# //    You should have received a copy of the GNU General Public License
# //    along with this program.  If not, see <http://www.gnu.org/licenses/>.
# ////////////////////////////////////////////////////////////////////////////////
# // Last modified 10/jun/2014 by vinicius_marangoni1@hotmail.com

uid=`id -u`
if [ "$uid" != "0" ]; then
  echo "Must be root to run this script. Use sudo /bin/bash first"
  exit 1
fi

apt-get -y install git makepasswd

for i in id chown chmod cut awk grep cat sed makepasswd ifconfig git iptables php touch mkdir update-rc.d su rm mv; do
  p=`which $i`
  if [ -x "$p" ]; then
    echo -n ""
  else
    echo command "$i" not found
    exit 1
  fi
done



apt-get -y --purge remove postgresql\*
rm -r /etc/postgresql/
rm -r /etc/postgresql-common/
rm -r /var/lib/postgresql/
userdel -r postgres
groupdel postgres

apt-get -y install postgresql postgresql-contrib postgresql-client apache2 libapache2-mod-php5 php5 php5-cli php5-cgi php5-gd php5-mcrypt php5-pgsql
apt-get -y install gcc g++ openjdk-7-jdk

cd /var/www/
git clone https://github.com/viniciusmarangoni/Boca_Python/
mv Boca_Python boca


privatedir=/var/www/boca/src/private
if [ ! -d $privatedir ]; then
  echo "Could not find directory $privatedir"
  exit 1
fi

apacheuser=www-data

postgresuser=postgres
id -u $postgresuser >/dev/null 2>/dev/null
if [ $? != 0 ]; then
  echo "User $postgresuser not found -- maybe you use another name (then update this script) or postgres is not installed"
  exit 1
fi

BOCASERVER=localhost
POSTGRESV=""
if [ ! -f /etc/init.d/postgresql ]; then
  POSTGRESV="-8.4"
fi
if [ ! -f /etc/init.d/postgresql$POSTGRESV ]; then
  echo "I did not find the correct version of postgres -- please check it and update this script"
  exit 1
fi

echo "host all all 127.0.0.1 255.255.255.255 md5" >> /etc/postgresql/*/main/pg_hba.conf
echo "host all all 0.0.0.0 0.0.0.0 md5" >> /etc/postgresql/*/main/pg_hba.conf

for i in `ls /etc/postgresql/*/main/postgresql.conf`; do
grep -q "^[^\#]*listen_addresses" $i
if [ $? != 0 ]; then
  echo "listen_addresses = '*'" >> $i
fi
done
for i in `ls /etc/postgresql/*/main/postgresql.conf`; do
grep -q "^[^\#]*max_connections" $i
if [ $? != 0 ]; then
  echo "max_connections = 100" >> $i
fi
done
for i in `ls /etc/postgresql/*/main/postgresql.conf`; do
grep -q "^[^\#]*maintenance_work_mem" $i
if [ $? != 0 ]; then
  echo "maintenance_work_mem = 64MB" >> $i
fi
done
for i in `ls /etc/postgresql/*/main/postgresql.conf`; do
grep -q "^[^\#]*shared_buffers" $i
if [ $? != 0 ]; then
  echo "shared_buffers = 32MB" >> $i
fi
done
for i in `ls /etc/postgresql/*/main/postgresql.conf`; do
grep -q "^[^\#]*work_mem" $i
if [ $? != 0 ]; then
  echo "work_mem = 10MB" >> $i
fi
done
for i in `ls /etc/postgresql/*/main/postgresql.conf`; do
grep -q "^[^\#]*effective_cache_size" $i
if [ $? != 0 ]; then
  echo "effective_cache_size = 1024MB" >> $i
fi
done

echo "You need to define a password to be used in the database."
echo -n "It is possible generate a random one. Want a random password "
read -p "[Y/n]? " OK
if [ "$OK" = "n" ]; then
 read -p "Enter DB password: " -s PASS
else
 PASS=`makepasswd --char 8`
 echo "The DB password is $PASS"
fi
echo "Keep the DB password safe!"

PASSK=`makepasswd --chars 20`
awk -v boca="$BOCASERVER" -v pass="$PASS" -v passk="$PASSK" '{ if(index($0,"[\"dbpass\"]")>0) \
  print "$conf[\"dbpass\"]=\"" pass "\";"; \
  else if(index($0,"[\"dbhost\"]")>0) print "$conf[\"dbhost\"]=\"" boca "\";"; \
  else if(index($0,"[\"basepass\"]")>0) print "$conf[\"basepass\"]=\"" pass "\";"; \
  else if(index($0,"[\"dbsuperpass\"]")>0) print "$conf[\"dbsuperpass\"]=\"" pass "\";"; \
  else if(index($0,"[\"key\"]")>0) print "$conf[\"key\"]=\"" passk "\";"; else print $0; }' \
  < $privatedir/conf.php > $privatedir/conf.php1
mv -f $privatedir/conf.php1 $privatedir/conf.php
echo "Deny from all" > $privatedir/.htaccess
chown -R $apacheuser /var/www/boca/src/
chmod -R u+w /var/www/boca/src/
iptables -F



/etc/init.d/apache2 restart
mkdir -p /var/run/postgresql
chown $postgresuser.$postgresuser /var/run/postgresql
/etc/init.d/postgresql$POSTGRESV restart
update-rc.d apache2 defaults
update-rc.d postgresql$POSTGRESV defaults

rm -f /tmp/.boca.tmp
su - $postgresuser -c "echo drop user bocauser | psql -d template1 >/dev/null 2>/dev/null"
su - $postgresuser -c "echo create user bocauser createdb password \'$PASS\' | psql -d template1"
su - $postgresuser -c "echo alter user bocauser createdb password \'$PASS\' | psql -d template1"
su - $postgresuser -c "echo UPDATE pg_database SET datistemplate = FALSE WHERE datname = \'template1\' | psql"
su - $postgresuser -c "echo DROP DATABASE template1 | psql"
su - $postgresuser -c "echo CREATE DATABASE template1 WITH TEMPLATE = template0 ENCODING = \'UNICODE\' | psql"
su - $postgresuser -c "echo UPDATE pg_database SET datistemplate = TRUE WHERE datname = \'template1\' | psql"

OK=y
grep -qi contestnumber /tmp/.boca.tmp
if [ $? == 0 ]; then
  OK=x
  while [ "$OK" != "y" -a "$OK" != "n" ]; do
  echo "====== An old database seems to exist. I can keep it, but it might not work with the version"
  echo -n "of BOCA being installed. May I erase all the content of the bocadb database [y/n]"
  OK=x
  read -p "?" OK
  done
fi
if [ "$OK" == "y" ]; then
cd /var/www/boca/src
php private/createdb.php
cd -
 echo "database renewed. Data on bocadb has been lost"
else
 echo "*** database not erased. Check if BOCA is compatible. You can always erase the database and"
 echo "*** fix the problem by running (as root) cd /var/www/boca; php private/createdb.php"
 echo "*** still, all data regarding BOCA in the database will be lost" 
fi

cat <<EOF > /etc/apache2/conf.d/boca
<Directory /var/www/boca>
AllowOverride Options AuthConfig Limit
Order Allow,Deny
Allow from all
AddDefaultCharset utf-8
</Directory>
<Directory /var/www/boca/private>
AllowOverride None
Deny from all
</Directory>
<Directory /var/www/boca/doc>
AllowOverride None
Deny from all
</Directory>
<Directory /var/www/boca/tools>
AllowOverride None
Deny from all
</Directory>
EOF

gcc -O2 /var/www/boca/tools/safeexec.c -o /usr/bin/safeexec
/etc/init.d/apache2 restart
/etc/init.d/postgresql restart

echo "configuration finished. Boca should be available at http://localhost/boca/"
echo "reboot might not be required, but is advised."
