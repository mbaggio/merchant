#
# Copy createdb.sql.example to createdb.sql
# then uncomment then set database name and username to create you need databases
#
# example: .env MYSQL_USER=appuser and needed db name is myshop_db
#
#    CREATE DATABASE IF NOT EXISTS `myshop_db` ;
#    GRANT ALL ON `myshop_db`.* TO 'appuser'@'%' ;
#
#
# this sql script will auto run when the mysql container starts and the $DATA_PATH_HOST/mysql not found.
#
# if your $DATA_PATH_HOST/mysql exists and you do not want to delete it, you can run by manual execution:
#
#     docker-compose exec mysql bash
#     mysql -u root -p < /docker-entrypoint-initdb.d/createdb.sql
#

CREATE DATABASE IF NOT EXISTS `merchants` COLLATE 'utf8_general_ci' ;

DROP USER IF EXISTS 'merchant_admin'@'%';
CREATE USER 'merchant_admin'@'%' IDENTIFIED WITH mysql_native_password BY 'password_123';
GRANT ALL ON `merchants`.* TO 'merchant_admin'@'%' ;

DROP USER IF EXISTS 'root'@'%';
CREATE USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'password_admin';
GRANT ALL ON `merchants`.* TO 'root'@'%' ;
# GRANT ALL ON `information_schema`.* TO 'merchant_admin'@'%' ;

FLUSH PRIVILEGES ;

