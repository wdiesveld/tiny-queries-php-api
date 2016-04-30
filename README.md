# TinyQueries PHP API

This is a derived version of [TinyQueries PHP-libs]. It can be used to set up a REST api based on a list of compiled queries.

## Installation instructions

* _Note 1_: It is assumed you already have an account for TinyQueries. If you don't have one, please [sign up] first.

* _Note 2_: The installation will be done by just downloading the ZIP and copy the files to your webserver. If you prefer to install TinyQueries using Composer we suggest to use [TinyQueries PHP-libs].

Follow the instructions below to setup a TinyQueries API:

1. Download the latest release of this repo as ZIP

1. Upload the files to the document-root of your server

1. Make a copy of the file ```config/config.template.xml``` and call it ```config/config.xml```. Fill in all required fields.

1. In the TinyQueries editor set the URL of your webserver. You can use localhost as well if your server runs on your laptop.

1. You can start creating queries. When you compile them, they are published at your webserver.


[TinyQueries PHP-libs]:https://github.com/wdiesveld/TinyQueries
[sign up]:https://www.tinyqueries.com/signup

