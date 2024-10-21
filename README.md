<p align="center">
  <img src="https://raw.githubusercontent.com/athenarc/clima/master/web/img/layouts/clima-logo-h.png" width="400px"/>
  <h1 align="center">Cloud Infrastructure Manager (CLIMA)</h1>
  <br />
</p>

### Prerequisites
In order to install SCHeMa you need:
* a PostgreSQL database server
* an OpenStack cloud infrastructure with a project and application credentials configured.
* a working [SCHeMa](https://github.com/athenarc/schema) installation in a non-standalone mode (for on-demand projects). Users must have the same username as in CLIMA, in order to be able to access their projects.

### Required PHP packages
The node running the installation of SCHeMa should have the following PHP packages installed:
* php-mbstring
* php-xml
* php-gd
* php-pgsql
* php-yaml



## Installing CLIMA

1. Download the CLIMA code from GitHub.
2. Navigate to the project directory and run the following command to install dependencies: ```composer install```

3. Create a postgres database named "clima" for user "clima".

4. Restore the .sql file inside the "database_schema" folder as user "postgres" to the database created in the previous step:
   ```sudo -u postgres psql -d clima -f <path_to_database_schema>/db_structure.sql```

5. Inside the project folder edit the following files:
* config/db.php: add the database credentials.
* config/params-template.php: rename to config/params.php and fill the information according to the description provided.

6. Log in with the username ```superadmin``` and the password ```superadmin```

## Creating a New User via Migration

1. Open the file ```migrations/migration.php``` and copy the code for inserting a user. 
   Adjust the following credentials as needed:

* username: Set the desired username.
* password: Set the desired password.
* email: Set the user’s email address.
* superadmin: Set to 1 if this user should have superadmin privileges.

2. Create a new migration file using the command: ```php yii migrate/create insert_new_user```
3. Open the newly created migration file in the migrations folder and paste the copied code.
4. Run the migration: ```php yii migrate```

