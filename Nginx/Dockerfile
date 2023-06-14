FROM debian:jessie

RUN sed -i s/deb.debian.org/archive.debian.org/g /etc/apt/sources.list
RUN sed -i 's|security.debian.org|archive.debian.org/debian-security/|g' /etc/apt/sources.list 
RUN sed -i '/stretch-updates/d' /etc/apt/sources.list

RUN apt-get update && apt-get install -y nginx

RUN usermod -u 1000 www-data
CMD ["nginx"]

EXPOSE 80
EXPOSE 443