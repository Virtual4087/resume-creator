FROM php:8.2-cli
WORKDIR /app
RUN mkdir -p /app/tmp \
	&& echo "session.save_path = /app/tmp" > /usr/local/etc/php/conf.d/session.ini
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
