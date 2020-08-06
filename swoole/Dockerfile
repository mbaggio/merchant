FROM zaherg/php-swoole:7.2
 
ENV APP_ENV localhost
ENV APP_DEBUG false
ENV APP_TIMEZONE UTC
ENV SWOOLE_HTTP_PORT 80
ENV SWOOLE_HTTP_HOST "0.0.0.0"
 
USER root
 
CMD ["php", "artisan","swoole:http","start"]
CMD ["php", "artisan", "config:clear"]
CMD ["php", "artisan", "migrate"]
