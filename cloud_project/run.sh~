#!/bin/bash

#build image
cd $2 &> /dev/null
sudo unzip -q "apps/$1" -d "apps/"
sudo docker build -t "$3" . &> error_log
SOCK_ID=`cat sock_id`
nc localhost $SOCK_ID < /dev/null
while [ $? -eq 0 ]
do
	SOCK_ID=`expr $SOCK_ID + 1`
	nc localhost $SOCK_ID < /dev/null
done
sudo docker run -name="$3" -d -p $SOCK_ID:80 -t "$3" &> error_log
sudo docker ps | grep "$3[^:]" &> error_log
 
if [ $? -eq 0 ]; then
echo "$3 running yeyee!!" &> error_log
else
echo "CRITICAL - $3 is not running. starting..." &> error_log
sudo docker images | grep "$3" &> error_log
if [ $? -eq 0 ]; then
sudo docker start "$3" &> error_log
fi
fi

ip_addr=`hostname -I | cut -d' ' -f1`
echo $ip_addr:$SOCK_ID
SOCK_ID=`expr $SOCK_ID + 1`
echo $SOCK_ID > sock_id
sudo rm -r "apps/*"
cd - &> /dev/null
