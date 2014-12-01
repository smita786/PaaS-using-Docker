
# Set the base image to Ubuntu
FROM shawn/ubuntu-precise-i386

# File Author / Maintainer
MAINTAINER Smita Snehal

RUN dpkg-divert --local --rename --add /sbin/initctl
RUN ln -s /bin/true /sbin/initctl

# Update the repository sources list
RUN sudo http_proxy=http://proxy.iiit.ac.in:8080 apt-get update

# Install apache2,php5 package (.deb)
RUN sudo http_proxy=http://proxy.iiit.ac.in:8080 apt-get install -y apache2 php5 libapache2-mod-php5

# Install mysql package (.deb)
RUN sudo http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install mysql-client mysql-server libapache2-mod-auth-mysql php5-mysql

RUN sed -i -e"s/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/" /etc/mysql/my.cnf

ADD ./database.sql /var/www/database.sql

ADD ./start-db.sh /usr/local/bin/start-db.sh
RUN chmod +x /usr/local/bin/start-db.sh

EXPOSE 3306

ADD tmp/. /var/www/

##################### INSTALLATION END #####################

# Set default container command
ENTRYPOINT service apache2 start;/usr/local/bin/start-db.sh;/bin/bash
