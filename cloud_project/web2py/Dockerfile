
# Set the base image to Ubuntu
FROM shawn/ubuntu-precise-i386

# File Author / Maintainer
MAINTAINER Smita Snehal

ADD setup-web2py-ubuntu.sh .

RUN chmod +x setup-web2py-ubuntu.sh

RUN sudo ./setup-web2py-ubuntu.sh

ADD tmp/. /home/www-data/web2py/applications/
RUN chown -R www-data:www-data /home/www-data/web2py/applications/

##################### INSTALLATION END #####################

# Set default container command
ENTRYPOINT service apache2 start;touch abc.txt;tail -f abc.txt
