FROM mysql/mysql-server:latest

#####################################
# Set Timezone
#####################################

ARG TZ=UTC

COPY docker-entrypoint-initdb.d/createdb.sql /docker-entrypoint-initdb.d/
COPY my.cnf /etc/mysql/conf.d/my.cnf

RUN chmod 0444 /etc/mysql/conf.d/my.cnf

CMD ["mysqld"]

EXPOSE 3306
