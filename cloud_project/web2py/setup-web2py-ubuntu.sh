echo "This script will:
1) install all modules need to run web2py on Ubuntu 14.04
2) install web2py in /home/www-data/
3) create a self signed sll certificate
4) setup web2py with mod_wsgi
5) overwrite /etc/apache2/sites-available/default
6) restart apache.
"
#!/bin/bash
# optional
# dpkg-reconfigure console-setup
# dpkg-reconfigure timezoneconf
# nano /etc/hostname
# nano /etc/network/interfaces
# nano /etc/resolv.conf
# reboot now
# ifconfig eth0

echo "installing useful packages"
echo "=========================="
http_proxy=http://proxy.iiit.ac.in:8080 apt-get update
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install ssh
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install zip unzip
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install tar
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install openssh-server
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install build-essential
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install python
#apt-get -y install python2.5
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install ipython
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install python-dev
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install postgresql
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install apache2
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install libapache2-mod-wsgi
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install python2.5-psycopg2
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install postfix
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install wget
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install python-matplotlib
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install python-reportlab
http_proxy=http://proxy.iiit.ac.in:8080 apt-get -y install mercurial
/etc/init.d/postgresql restart

# optional, uncomment for emacs
# apt-get -y install emacs

# optional, uncomment for backups using samba
# apt-get -y install samba
# apt-get -y install smbfs

echo "downloading, installing and starting web2py"
echo "==========================================="
cd /home
mkdir www-data
cd www-data
rm web2py_src.zip*
http_proxy=http://proxy.iiit.ac.in:8080 wget http://web2py.com/examples/static/web2py_src.zip
unzip web2py_src.zip
mv web2py/handlers/wsgihandler.py web2py/wsgihandler.py
chown -R www-data:www-data web2py

echo "setting up apache modules"
echo "========================="
a2enmod ssl
a2enmod proxy
a2enmod proxy_http
a2enmod headers
a2enmod expires
a2enmod wsgi
mkdir /etc/apache2/ssl

echo "creating a self signed certificate"
echo "=================================="
openssl genrsa 1024 > /etc/apache2/ssl/self_signed.key
chmod 400 /etc/apache2/ssl/self_signed.key
openssl req -new -x509 -nodes -sha1 -days 365 -key /etc/apache2/ssl/self_signed.key -out /etc/apache2/ssl/self_signed.cert -subj "/C=IN/ST=AP/L=HYD/O=Global Security/OU=IT Department/CN=paas.com"
openssl x509 -noout -fingerprint -text < /etc/apache2/ssl/self_signed.cert > /etc/apache2/ssl/self_signed.info

echo "rewriting your apache config file to use mod_wsgi"
echo "================================================="
echo '
NameVirtualHost *:80
NameVirtualHost *:443
# If the WSGIDaemonProcess directive is specified outside of all virtual
# host containers, any WSGI application can be delegated to be run within
# that daemon process group.
# If the WSGIDaemonProcess directive is specified
# within a virtual host container, only WSGI applications associated with
# virtual hosts with the same server name as that virtual host can be
# delegated to that set of daemon processes.
WSGIDaemonProcess web2py user=www-data group=www-data processes=1 threads=1

<VirtualHost *:80>
  WSGIProcessGroup web2py
  WSGIScriptAlias / /home/www-data/web2py/wsgihandler.py
  WSGIPassAuthorization On

  <Directory /home/www-data/web2py>
    AllowOverride None
    Order Allow,Deny
    Deny from all
    <Files wsgihandler.py>
      Allow from all
    </Files>
  </Directory>

  AliasMatch ^/([^/]+)/static/(?:_[\d]+.[\d]+.[\d]+/)?(.*) \
           /home/www-data/web2py/applications/$1/static/$2
  <Directory /home/www-data/web2py/applications/*/static/>
    Options -Indexes
    Order Allow,Deny
    Allow from all
  </Directory>

  <Location /admin>
  Allow from all
  </Location>

  <LocationMatch ^/([^/]+)/appadmin>
  Allow from all
  </LocationMatch>

  CustomLog /var/log/apache2/access.log common
  ErrorLog /var/log/apache2/error.log
</VirtualHost>

<VirtualHost *:443>
  SSLEngine on
  SSLCertificateFile /etc/apache2/ssl/self_signed.cert
  SSLCertificateKeyFile /etc/apache2/ssl/self_signed.key

  WSGIProcessGroup web2py
  WSGIScriptAlias / /home/www-data/web2py/wsgihandler.py
  WSGIPassAuthorization On

  <Directory /home/www-data/web2py>
    AllowOverride None
    Order Allow,Deny
    Deny from all
    <Files wsgihandler.py>
      Allow from all
    </Files>
  </Directory>

  AliasMatch ^/([^/]+)/static/(?:_[\d]+.[\d]+.[\d]+/)?(.*) \
        /home/www-data/web2py/applications/$1/static/$2

  <Directory /home/www-data/web2py/applications/*/static/>
    Options -Indexes
    ExpiresActive On
    ExpiresDefault "access plus 1 hour"
    Order Allow,Deny
    Allow from all
  </Directory>

  CustomLog /var/log/apache2/access.log common
  ErrorLog /var/log/apache2/error.log
</VirtualHost>
' > /etc/apache2/sites-available/default

# echo "setting up PAM"
# echo "================"
# sudo apt-get install pwauth
# sudo ln -s /etc/apache2/mods-available/authnz_external.load /etc/apache2/mods-enabled
# ln -s /etc/pam.d/apache2 /etc/pam.d/httpd
# usermod -a -G shadow www-data

echo "restarting apache"
echo "================"

/etc/init.d/apache2 restart
cd /home/www-data/web2py
sudo -u www-data python -c "from gluon.widget import console; console();"
sudo -u www-data python -c "from gluon.main import save_password; save_password('admin',80)"
echo "done!"