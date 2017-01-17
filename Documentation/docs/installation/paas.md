# PaaS
__*PaaS*__ stands for: __P__latform __a__s __a__ __S__ervice.

A PaaS is a service that give life to your application, which is hosted in a
cloud (often sandboxed) environment, giving you additional protection to your
application and abstracting away from you the problem of scaling large-scale
server intensive applications.

A PaaS provider ofter adds to its service a set of utilities that simplify your
life, especially with automatic content delivery and/or application update:
for example Heroku automatically Sync your application with its GitHub repository
and automatically deploy your application on each push if it passes the
automatic integration testing set!

A small list of supported PaaS may be:
 - Heroku
 - OpenShift
 - Google AppEngine
 - PagodaBox
 - elastx
 - Microsoft Azure

But there are many, many more of them: everything that can run PHP 5.4 will work!


## Getting Started
Gishiki is tested costantly with Heroku because Heroku is fantastic
and empowers your application with a lot of simple tools without forcing you to
maintain a server or a container!

Setting up a perfectly working Heroku/OpenShift application is super-easy:

   - Register & Login to your PaaS provider website
   - Create a new application using your PaaS provider website
   - Setup auto-deploy using your PaaS provider website
   - Create a new application using [composer](composer.md)

Edit locally your application, commit and when a *git push* is performed to GitHub your
Heroku/openShift application is automatically updated! Have fun <3.


## Database
You are free to host your database everywhere, but Heroku, OpenShift and many
others PaaS are providing database support as simple add-ons to your application,
and Gishiki is able to take advantage of those add-ons!

For example you can pick the "Heroku Postgres" add-on to have for free a PostgreSQL
database binded with your application!