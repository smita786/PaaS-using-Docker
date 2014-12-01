#!/bin/bash

#build image
cd $2 &> /dev/null
cd $4 &> /dev/null
echo "deploying ".$4." app.. " &> ../error_log
sudo mkdir tmp &> /dev/null
sudo unzip -q "../apps/$1" -d "tmp/"
sudo docker build -t "$3" . &> ../error_log
SOCK_ID=`cat ../sock_id`
nc localhost $SOCK_ID < /dev/null
while [ $? -eq 0 ]
do
	SOCK_ID=`expr $SOCK_ID + 1`
	nc localhost $SOCK_ID < /dev/null
done
if [ "$4" == 'php_mysql' ]
then
    echo "hi " &> ../error_log
    sudo docker run -i --env user="$5" --env pass="$6" -name="$3" -d -p $SOCK_ID:80 -t "$3" &> ../error_log
else
    sudo docker run -i -name="$3" -d -p $SOCK_ID:80 -t "$3" &> ../error_log
fi
sleep 5
sudo docker ps | grep "$3[^:]" &> ../error_log
 
if [ $? -eq 0 ]; then
echo "$3 running yeyee!!" &> ../error_log
else
echo "CRITICAL - $3 is not running. starting..." &> ../error_log
sudo docker images | grep "$3" &> ../error_log
if [ $? -eq 0 ]; then
sudo docker start "$3" &> ../error_log
fi
fi

ip_addr=`hostname -I | cut -d' ' -f1`
echo $ip_addr:$SOCK_ID
SOCK_ID=`expr $SOCK_ID + 1`
echo $SOCK_ID > ../sock_id
sudo rm -r "tmp" &> ../error_log
sudo rm -r ../apps/* &> ../error_log
echo "Oops! couldn't create git repo! Token mismatch!" &> ../error_log
cd - &> /dev/null
