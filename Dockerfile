FROM php:5.6-apache

RUN mkdir -p /opt/php-seating-chart
COPY . /opt/php-seating-chart/

CMD ["/bin/sh", "-c", "php /opt/php-seating-chart/reserveSeats.php < /opt/php-seating-chart/reservations.txt" ]