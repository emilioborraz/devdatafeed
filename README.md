# Dev Data feed

## Deploying via command line

1. `cd ~/public_html/api.borraz.com/`
2. `shopt -s extglob`
3. `rm -rf -- !(.htaccess)`
4. `git clone --depth=1 https://github.com/emilioborraz/devdatafeed.git`
5. `mv devdatafeed/* .`
6. `rm -rf devdatafeed/`
7. `composer install --ignore-platform-reqs`
8. Update the .env file (if needed).
