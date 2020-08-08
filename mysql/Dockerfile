FROM mysql/mysql-server:latest

#####################################
# Set Timezone
#####################################

ARG TZ=UTC

# initial main sql files
# COPY docker-entrypoint-initdb.d/createdb.sql /docker-entrypoint-initdb.d/
COPY my.cnf /etc/my.cnf
RUN chmod 0644 /etc/my.cnf

# start service
CMD ["mysqld"]

# wait until finish
ADD waitForMySQL.sh /root/
RUN chmod +x /root/waitForMySQL.sh

EXPOSE 3306
