# Installation
Setting up a project using Gishiki is really simple, even a child could be able to do it:
however, in order to succeed you need to complete steps listed on the [Composer](composer.md) page.

For the sake of simplicity, the application, while in development stage, can be
run using the [php built-in webserver](basic_php.md).

To setup a production environment you should read the folling chapters.


## Windows
If you want to test Gishiki or develop your application on Windows, you can
choose between:
* Installing [XAMPP](https://www.apachefriends.org/) and manually enable needed
extensions on php.ini (suggested for ancient PC that cannot run a Virtual Machine)
* Installing [VirtualBox](https://www.virtualbox.org/) and [Vagrant](https://www.vagrantup.com/)
and use the provided Vagrantfile to setup a fully-working linux environment!


## Mac OS X
If you are willing to *develop* your application on a Mac system you can either use
[XAMPP](https://www.apachefriends.org/) or the PHP built-in webserver.


## Linux
Every production server runs on a linux or a container inside linux, this is why
every instruction you'll find are written for linux and tested with Ubuntu 16.04.

If you are a newcomer I suggest you to follow the [PHP 7.0 & nginx](php_7_nginx.md)
tutorial I have written for you.


## Virtual Machine
If you don't want to pollute your desktop environment you can use a virtualization
program, like [VirtualBox](https://www.virtualbox.org/), to install [Ubuntu Server](http://www.ubuntu.com/download/server) and
follow the tutorial on that virtual machine!


## Let's go!
If you want something that works in a few seconds than the [PaaS](paas.md) page
is the right manual page!
