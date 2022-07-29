# Instruction:
# cd <project_root>/docker
# docker build -t azumasv/barotraumamodportal:devwithlando-php-fpm .
# docker push azumasv/barotraumamodportal:devwithlando-php-fpm
# Done!

# Inherit Lando default container.
FROM devwithlando/php:8.1-fpm-4

# Install SteamCMD.
RUN apt-get -q -y update && \
    apt-get install software-properties-common -y && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 40976EAF437D05B5 && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 3B4FE6ACC0B21F32 && \
    dpkg --add-architecture i386 && \
    add-apt-repository "deb http://archive.ubuntu.com/ubuntu xenial main universe multiverse" && \
    add-apt-repository "deb http://archive.ubuntu.com/ubuntu xenial-updates main universe multiverse" && \
    add-apt-repository "deb http://archive.ubuntu.com/ubuntu xenial-security main universe multiverse" && \
    apt-get -q -y update && \
    echo steam steam/question select "I AGREE" | debconf-set-selections && \
    echo steam steam/license note '' | debconf-set-selections && \
    apt-get install lib32gcc-s1 steamcmd -y && \
    ln -sf /usr/games/steamcmd /usr/bin/steamcmd && \
    apt-get --purge remove software-properties-common -y && \
    apt-get clean

RUN steamcmd +quit
