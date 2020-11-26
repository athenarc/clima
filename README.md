<p align="center">
  <img src="https://raw.githubusercontent.com/athenarc/clima/master/web/img/layouts/clima-logo-h.png" width="400px"/>
  <h1 align="center">Cloud Infrastructure Manager (CLIMA)</h1>
  <br />
</p>

### Prerequisites
In order to install SCHeMa you need:
* a PostgreSQL database server
* an OpenStack cloud infrastructure with a project and application credentials configured.
* a working[SCHeMa](https://github.com/athenarc/schema) installation  in a non-standalone mode (for on-demand projects). Users must have the same username as in CLIMA, in order to be able to access their projects.

### Required PHP packages
The node running the installation of SCHeMa should have the following PHP packages installed:
* php-mbstring
* php-xml
* php-gd
* php-pgsql
* php-yaml



## Installing CLIMA

1. Install the Yii2 framework([tutorial](https://www.yiiframework.com/doc/guide/2.0/en/start-installation)) and install the following plugins:
  * [Webvimark User management](https://github.com/webvimark/user-management) without migrating the database.
  * [DatePicker](https://demos.krajee.com/widget-details/datepicker)
  * [Yii2 Bootstrap4](https://github.com/yiisoft/yii2-bootstrap4)
  * [Yii http requests](https://github.com/yiisoft/yii2-httpclient)

2. Download the CLIMA code from GitHub and replace the files inside the Yii project folder.

3. Create a postgres database named "clima" for user "clima".

4. Restore the .sql file inside the "database_schema" folder as user "postgres" to the database created in the previous step:
  ```sudo -u postgres psql -d clima -f <path_to_database_schema>/db_structure.sql```

5. Inside the project folder edit the following files:
  * config/db.php: add the database credentials.
  * config/params-template.php: rename to config/params.php and fill the information according to the description provided.


