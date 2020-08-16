FROM zaherg/php-swoole:7.2
 
ENV APP_ENV localhost
ENV APP_DEBUG false
ENV APP_TIMEZONE UTC
ENV SWOOLE_HTTP_PORT 80
ENV SWOOLE_HTTP_HOST "0.0.0.0"
 
USER root

ADD ./run.sh /tmp/
RUN chmod +x /tmp/run.sh

# wait until finish
ADD waitForComposer.sh /root/
RUN chmod +x /root/waitForComposer.sh
ADD waitForURL.sh /root/
RUN chmod +x /root/waitForURL.sh

ENTRYPOINT ["/tmp/run.sh"]