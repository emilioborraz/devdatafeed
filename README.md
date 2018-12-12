# Dev Data feed

## Deploying via command line
cd ~/public_html/api.borraz.com/
shopt -s extglob
rm -rf -- !(.htaccess)
git clone --depth=1 https://github.com/emilioborraz/devdatafeed.git
mv devdatafeed/* .
rm -rf devdatafeed/
composer install --ignore-platform-reqs
# update the .env file (nano .env)