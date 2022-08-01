# Instruction:
# cd <project_root>/docker
# docker build -t azumasv/barotraumix:lando-php-8.1 .
#
# You might want to test it with:
# docker run -d -t -i --rm azumasv/barotraumix:lando-php-8.1
# docker exec -it <container_id> /bin/bash (use docker ps -a to grab container id value).
#
# Push new changes.
# docker push azumasv/barotraumix:lando-php-8.1
# Done!

# Inherit Lando default container.
FROM devwithlando/php:8.1-fpm-4

# Prepare to install Steam CMD.
RUN apt-get -q -y update && \
    apt-get install software-properties-common sudo -y && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 40976EAF437D05B5 && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 3B4FE6ACC0B21F32 && \
    dpkg --add-architecture i386 && \
    add-apt-repository "deb http://archive.ubuntu.com/ubuntu xenial main universe multiverse" && \
    add-apt-repository "deb http://archive.ubuntu.com/ubuntu xenial-updates main universe multiverse" && \
    add-apt-repository "deb http://archive.ubuntu.com/ubuntu xenial-security main universe multiverse" && \
    apt-get -q -y update && \
    useradd steam && \
    echo steam:steam | sudo chpasswd -e && \
    usermod -a -G www-data steam && \
    mkdir /home/steam && \
    usermod -d /home/steam steam && \
    chown -R steam:www-data /home/steam && \
    echo "steam ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Install SteamCMD as steam user.
USER steam
RUN cd ~/ && \
    echo steam steam/question select "I AGREE" | sudo debconf-set-selections && \
    echo steam steam/license note '' | sudo debconf-set-selections && \
    sudo apt-get install lib32gcc-s1 steamcmd -y

# Clear garbage.
USER root
RUN ln -sf /usr/games/steamcmd /usr/bin/steamcmd && \
    apt-get --purge remove software-properties-common -y && \
    apt-get clean

# Initialize steam.
RUN runuser -l  steam -c "steamcmd +quit"
# The last line is most problematic. That's why it was separated.
