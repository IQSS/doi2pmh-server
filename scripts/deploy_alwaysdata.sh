sudo apt update -y && sudo apt install -y openssh-client unzip rsync

# Setup ssh
eval $(ssh-agent -s)
echo "$SSH_PRIVATE_KEY_ALWAYSDATA" | base64 -d | tr -d '\r' | ssh-add - > /dev/null
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo "$SSH_KNOWN_HOSTS_ALWAYSDATA" | base64 -d > ~/.ssh/known_hosts
chmod 644 ~/.ssh/known_hosts

ssh doi2pmh@ssh-doi2pmh.alwaysdata.net "cd www && rm -rf src config public templates translations"
rsync -r --exclude=node_modules * doi2pmh@ssh-doi2pmh.alwaysdata.net:www/

ssh doi2pmh@ssh-doi2pmh.alwaysdata.net "cd www && php bin/console cache:clear --env=prod --no-debug"
#ssh doi2pmh@ssh-doi2pmh.alwaysdata.net "cd www && php bin/console doctrine:migrations:status"
#ssh doi2pmh@ssh-doi2pmh.alwaysdata.net "cd www && php bin/console doctrine:migrations:migrate -q"

