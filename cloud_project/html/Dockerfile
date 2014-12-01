
# Set the base image to Ubuntu
FROM shawn/ubuntu-precise-i386

# File Author / Maintainer
MAINTAINER Smita Snehal

# Update the repository sources list
RUN sudo http_proxy=http://proxy.iiit.ac.in:8080 apt-get update

# Install apache2,php5 package (.deb)
RUN sudo http_proxy=http://proxy.iiit.ac.in:8080 apt-get install -y apache2

ADD tmp/. /var/www/

##################### INSTALLATION END #####################

# Set default container command
ENTRYPOINT service apache2 start;touch abc.txt;tail -f abc.txt
