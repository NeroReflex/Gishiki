# Composer
As promised Gishiki is not meant to replace your favourite tools:
you can still use all of them!

Doctrine? Propel? Zend framework components? Symfony components? No problem!

You have to install them and you do that using composer! If you don't have composer run:

```shell
sudo wget https://getcomposer.org/composer.phar
sudo chmod +x composer.phar
sudo mv composer.phar /usr/local/bin/composer
```

Remember that composer is essential to run Gishiki: composer is what loads the
entire framework, manages its dependencies and keep updated the framework!


## Bootstrapping an application
You like digging immediatly into development? No problem!

You will have to use composer to start up your new project!
```shell
composer init # remember to specify neroreflex/gishiki
composer install --no-dev
./vendor/bin/gishiki new application
```

nice and easy! Good work.