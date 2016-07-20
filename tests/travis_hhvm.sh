#!/bin/bash
sudo add-apt-repository ppa:ubuntu-toolchain-r/test -y
sudo update-alternatives --remove-all gcc 
sudo update-alternatives --remove-all g++ 
sudo apt-get update
sudo apt-get install g++-4.8 gcc-4.8 hhvm-dev -y
sudo apt-get upgrade -y && sudo apt-get dist-upgrade -y
sudo update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-4.8 20
sudo update-alternatives --install /usr/bin/g++ g++ /usr/bin/g++-4.8 20
sudo update-alternatives --config gcc
sudo update-alternatives --config g++
git clone https://github.com/mongodb/mongo-hhvm-driver.git
cd mongo-hhvm-driver
git submodule sync
git submodule update --init --recursive
hphpize
cmake .
make configlib
make -j 5
sudo make install
cd ../