FROM php:5.6-apache

RUN mkdir -p /opt/php-seating-chart
COPY ./seatingChart.php reservationDriver.php reservations.txt /opt/php-seating-chart/

CMD ["/bin/sh", "-c", "php /opt/php-seating-chart/reservationDriver.php < /opt/php-seating-chart/reservations.txt" ]