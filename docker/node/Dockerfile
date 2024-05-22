FROM node:18

RUN apt-get update && \
	apt-get install -y \
		curl \
		apt-transport-https

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
	echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update && apt-get install -y yarn

ENV SYMFONY_ROOT="/var/www/html"

WORKDIR $SYMFONY_ROOT

# Vhost, crontab, PHP config and other system configuration files.
COPY files/ /
RUN chmod u+x /node-install.sh

CMD ["yarn","watch"]
