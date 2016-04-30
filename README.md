# TinyQueries PHP API

This is a derived version of [TinyQueries PHP-libs]. It can be used to set up a REST api based on a list of compiled queries.

## Installation instructions

* _Note 1_: It is assumed you already have an account for TinyQueries. If you don't have one, please [sign up] first.

* _Note 2_: The installation will be done by just downloading the ZIP and copy the files to your webserver. If you prefer to install TinyQueries using Composer we suggest to use [TinyQueries PHP-libs].

Follow the instructions below to setup a TinyQueries API:

1. Download the latest release of this repo as ZIP

1. Upload the files to a folder inside the document-root of your server. For example, place it in a folder such that you can access it
through ```http://www.myserver.com/api/```

1. Make a copy of the file ```config/config.template.xml``` and call it ```config/config.xml```. Fill in all required fields.

1. In the TinyQueries editor go to Config > Publish settings. In the field 'Publish to' set the URL of the api, so for example ```http://www.myserver.com/api```. 
You can use localhost as well if your server runs on your laptop.

1. If you don't use Apache, ensure you do the same URL-rewriting as is done in ```.htaccess```. 
   Furthermore ensure that the folders ```config```, ```libs``` and ```queries``` are not accessible.

1. You can start creating queries. When you compile them, they are published to your webserver. The queries can be called using the api 
by ```http://www.myserver.com/api/{myQuery}```. So for example  ```http://www.myserver.com/api/helloWorld``` should work after you compile for the first time.


[TinyQueries PHP-libs]:https://github.com/wdiesveld/TinyQueries
[sign up]:https://www.tinyqueries.com/signup

