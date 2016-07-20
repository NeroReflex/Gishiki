#!/bin/bash
sudo apt-get install -y hhvm-dev
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