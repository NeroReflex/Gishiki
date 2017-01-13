# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
   config.vm.box = "bento/ubuntu-16.04"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
   config.vm.network "public_network"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
   config.vm.provider "virtualbox" do |vb|
     # Display the VirtualBox GUI when booting the machine
     vb.gui = false
  
     vb.name = "Gishiki"
     # Customize the amount of memory on the VM:
     vb.memory = "512"

     # Avoid ubuntu network problems at boot
     vb.customize ["modifyvm", :id, "--cableconnected1", "on"]
     
     # Limit CPU usage
     vb.customize ["modifyvm", :id, "--cpuexecutioncap", "65"]
   end
  
  # Enable USB Controller on VirtualBox
  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--usb", "on"]
    vb.customize ["modifyvm", :id, "--usbehci", "on"]
  end

  ###############################################################
   config.vm.provision "shell", inline: <<-SHELL
     printf "\n\nInstalling software\n"
     sudo apt-get update && sudo apt-get upgrade -y
     sudo apt-get -y install curl git openssl pkg-config libssl-dev python wget zlib1g-dev unzip openssh-client php7.0 php7.0-cli php7.0-curl php7.0-json php7.0-xml php7.0-dev
     
     printf "\n\nInstalling MongoDB\n"
     sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
     echo "deb http://repo.mongodb.org/apt/ubuntu xenial/mongodb-org/3.4 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.4.list
     sudo apt-get update
     sudo apt-get install -y mongodb-org
     
     printf "\n\nStarting MongoDB\n"
     sudo service mongod start
     
     printf "\n\nInstalling PECL PHP extensions\n"
     sudo rm -f /usr/local/etc/php/conf.d/pecl.ini
     sudo touch /usr/local/etc/php/conf.d/pecl.ini
     sudo chmod 0775 -R /usr/local/etc/php/conf.d
     pecl config-set php_ini /usr/local/etc/php/conf.d/pecl.ini
     pear config-set php_ini /usr/local/etc/php/conf.d/pecl.ini
     pecl install mongodb-1.1.8
     pecl install xdebug-2.4.0
     
     printf "\n\nInstalling PHPUnit\n"
     curl -L https://phar.phpunit.de/phpunit.phar -o /usr/local/bin/phpunit
     chmod +x /usr/local/bin/phpunit
     
     printf "\n\nInstalling Composer\n"
     curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
     
     printf "\n\nInstalling pip and mkdocs\n"
     curl -o- https://bootstrap.pypa.io/get-pip.py | sudo python
     sudo pip install mkdocs
     
     printf "\n\nPreparing MongoDB\n"
     mongo localhost:27017/gishiki /vagrant/tests/SetupTestingMongo.js

     # link volume to home user folder
     ln -s /vagrant Gishiki
     
     printf "\n\nSystem info:\n"
     php -i

     printf "\n\n\n\nThe box is ready. Now simply run \"vagrant ssh\" to connect!\n"
     
   SHELL
   
   
   
end
