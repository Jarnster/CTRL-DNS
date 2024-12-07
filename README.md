# ZenDNS
 Local-hosted DNS service written in PHP and Python

## How to run?

1) Copy "config.default.json" in /data, make your own configuration (change the ADMIN_PWD to something else if you want to use another password), then rename the file to "config.json"
2) Use Docker-compose to run /dns ("docker-compose build", then "docker-compose up")
3) Use PHP to serve /web
4) Login to the admin panel with default password: "zendns" or the password you chose to set in "config.json"

Note: If you want to change the ADMIN_PWD later on when the system has already generated the ADMIN_PWD_HASH, then change the AMDIN_PWD_HASH to null after changing the ADMIN_PWD to let the system recalculate the hash for the password.