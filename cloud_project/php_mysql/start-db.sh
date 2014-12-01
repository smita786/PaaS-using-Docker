#!/bin/bash
# Now the provided user credentials are added
/usr/sbin/mysqld &
sleep 5
echo "Creating user"
if [ "$user" != "root" ]
then
    if [ "$user" != "admin" ]
    then
        echo "CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass'" | mysql --default-character-set=utf8
    fi
    echo "GRANT SELECT ON *.* TO '$user'@'localhost' IDENTIFIED BY '$pass' WITH GRANT OPTION; FLUSH PRIVILEGES" | mysql --default-character-set=utf8
    echo "GRANT ALL PRIVILEGES ON *.* TO '$user'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES" | mysql --default-character-set=utf8
else
    mysqladmin -u $user password $pass
fi
# And we restart the server to go operational
mysqladmin shutdown
sleep 5
echo "Starting MySQL Server"
/usr/sbin/mysqld &
sleep 5
mysql -u $user -p$pass < /var/www/database.sql