# Setting up local dev environment from scratch
1. Prepare an empty directory and `cd` into it. Let's call it *chatmatic root directory*. After executing these steps, that directory will contain a bunch of repositories and other directories, best to avoid mixing it with other projects. After all, the directory structure will look like this:
```
@mbp:~/code$ tree -L 1 chatmatic/
chatmatic/
├── chatmatic
├── chatmatic_ci
├── chatmatic_pipeline
├── ci -> chatmatic_ci/
└── storage
```

2. Clone this repo and `cd` into it.
```
git clone git@github.com:travisstephenson/chatmatic_ci.git && cd chatmatic_ci
```

3. Run `./init`.
Save the steps `./init` outputs at the end somewhere - once you start running the commands it asks for, a lot of output will be produced to the terminal, you might not be able to scroll back to see it again. You can always re-run `./init` command as well - there is no need to repeat the steps from the beginning after re-running it, just continue with installation steps.
`./init` clones remaining repositories to the *chatmatic root directory*, creates storage dir, and sets some permissions. Following the steps it asks for at the end will build docker containers and bring the environment to a working state.

## Chatmatic-wide refactoring TODO's
- use .env everywhere consistently, forget .ini and hardcoded parameters
- replace files based queues with better system
- move persistent storage to S3
- get rid of hacks like chatmatic code included in python container
- anything that's left that forces that app to be able to see each other files as their local

## Local URLs
*Hosts file entry on local/host machine (your laptop/desktop) for `127.0.0.1  admin api` required*

Repository | URL
---------- | ---
**chamatic_web** | http://localhost
**chatmatic** | http://admin
**adminer** | http://localhost/adminer

## Repositories and their respective branches

Repository | Staging Branch | Production Branch
---------- | -------------- | -----------------
**chatmatic** | staging | master
**chatmatic_pipeline** | test | production
**chatmatic_websocket_server** | staging | master
**chatmatic_web** | staging | new-server

## Container Names

Container Name | Description
-------------- | -----------
**pipeline** | python webhook->job creator
**chatprocess** | python job processor
**websocket** | websocket server
**chatmatic_web** | frontend UI and supporting API
**laravel** | laravel web app
**adminer** | postgres web client
**nginx** | web server
**postgres** | database
**papertrail** | logging
**shmhost** | shared 'host' to the two python daemons, pipeline and chatprocess

## Misc Commands and Management/Deployment Processes

The following commands are assumed to be run from within the `chatmatic_ci` directory.

- In order to update a single repository...
    - PHP based repositories can be updated by issuing a `git pull` from their respective directories (as shown in the above directory tree)
        - mferrara/chatmatic (laravel application)
        - mferrara/chatmatic_web (frontend)
    - Python based repositories are daemons and must be restarted by issuing `./restart CONTAINER_NAME` from the chatmatic_ci directory _after_ the `git pull` from within their respective directories
        
        Example: `./restart chatprocess` to restart the `chatprocess` container
        - mferrara/chatmatic_pipeline
        - mferrara/chatmatic_websocket_server
        
- Pull all repositories in one command `./pullall`
- View current branches on all repositories `./branches`
- Build `./build`
- Down `./down`
- Execute a command on a specific container `./exec CONTAINER_NAME COMMAND` 

    Example: `./exec laravel php artisan migrate --force`
    
    Get a bash client in a specific container: `./exec CONTAINER_NAME bash`
- View live logs `./logs`
- Pull all, build, up & logs `./pbul` 
- Ps `./ps`
- Restart `./restart`
- Run `./run`
- Start `./start`
- Stop `./stop`
- Up `./up`
- Webhook forwarding from staging server/app id to your local pipeline: `./attach`

    You'll know it's working if it outputs something like `Version: 0.16.0` and doesn't go back to the prompt.
    
    This will allow you to use your http://localhost instance of chatmatic like it's live.
- Installing composer dependencies on `chatprocess` container `./exec chatprocess composer install -d /app/chatmatic`
- Migrating database `./exec laravel php artisan migrate --force`
- Importing database
    1. git pull
    2. ./stop postgres
    3. rm -rf ../storage/postgres
    4. Replace chatmatic_ci/postgres/docker-entrypoint-initdb.d/db.sql with db_dump sql file you want to import
    5. ./up postgress

## SSL notes
At the moment of writing this note, SSL setup works like this. The `./init` scrit creates a self signed certificate during first run. This certificate will throw browser errors, but we need any cert at this point. In order to get a valid certificate, the self-signed one can be replaced with Let's Encrypt.
#### Why doesn't the `./init` script do Let's Encrypt autoamtically then?
A few reasons:
1) Local envs would not be able to handle Let's Encrypt at all because they are not exposed to the internet.
2) Remote ens (installed on a public server like Digital Ocean), may not be able to lass Let's Encrypt validation because of incompelte DNS setup.
3) While running `./init` we want to make sure SSL configuration will not fail, and that's what self-signed is good for, it always works, at least from this perspective.
#### Changing self-signed certificate to Let's Encrypt.
Assuming DNS is configured properly this can be done with two steps:
1) Use `certbot` docker image in order to request the certificate like this:
```
$ . .env && docker run --rm -it --network chatmatic --name letsencrypt -v $(cd $STORAGE && pwd)/letsencrypt:/etc/letsencrypt certbot/certbot certonly -d $(read -p "Common name for the certificate: " CN; echo -n $CN) --standalone --register-unsafely-without-email --agree-tos
Common name for the certificate: name.domain.com    # <-------- type your domain here
```
2) Edit nginx config file and point it at Let's Encrypt certificate.
Assuming the above is successfull, it will display the paths to certificates and key, or you can always print them again with this commmand:
```
$ . .env && docker run --rm -it --network chatmatic --name letsencrypt -v $(cd $STORAGE && pwd)/letsencrypt:/etc/letsencrypt certbot/certbot certificates
```
Once you know the paths, navigate to the `storage/letsencrypt` directory, edit `nginx_ssl_directives.inc` and point nginx at the new paths, this command will help:
```
$ . .env && vi "$STORAGE/letsencrypt/nginx_ssl_directives.inc"

# This file should end up similar to this:
ssl_certificate     /etc/letsencrypt/live/staging.chatdeploy.com/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/staging.chatdeploy.com/privkey.pem;
```
3) Restart nginx container: `./restart nginx`

#### If something goes wrong
If nginx fails to start after all these changes, you can just delete the `$STORAGE/letsencrypt/nginx_ssl_directives.inc`, and run `./init` again. If the file is missing, ./init will re-create self-signed certificate and re-populate `$STORAGE/letsencrypt/nginx_ssl_directives.inc`.

#### Updating certbot docker image
If for some reason you need to force certbot image update, just delete the cached image:
```
docker image rm certbot/certbot
```

#### Adding more domains to the certificate (expanding the certificate)
Run the same command when creating the first cert, when asked for the domain name, type all of the domains that should be in the certificate (include the ones that are alredy covered too). This way certbot will extend the certificate. It's importnat to keep all of the certificates in a single file.
```
$ . .env && docker run --rm -it --network chatmatic --name letsencrypt -v $(cd $STORAGE && pwd)/letsencrypt:/etc/letsencrypt certbot/certbot certonly -d $(read -p "Common name for the certificate: " CN; echo -n $CN) --standalone --register-unsafely-without-email --agree-tos
Common name for the certificate: name.domain.com,name2.domain.com    # <-------- type your domains here, comma separated
```

#### Renewing Let's Encrypt certificates
Let's Encrypt certificate lasts for 3 months only. Example cronjob that will attempt renewal once a week is in `nginx/letsencrypt_cronjob` in this repo.

### Deployment

##### Laravel (admin portal + UI API + Database Migrations)
```
cd ../chatmatic 
git pull 
cd ../chatmatic_ci
./exec laravel composer install
./exec laravel composer dumpautoload 
./exec laravel php artisan migrate --force 
./exec laravel php artisan horizon:purge
./exec laravel php artisan horizon:terminate
./exec laravel php artisan queue:restart 
``` 

##### React (UI)
```
cd ../chatmatic_react 
git pull
cd ../chatmatic_ci
./build react
./up react
```

### Local URL's
- Front end - https://app 

(at privacy warning when using chrome type "thisisunsafe")

and in console/terminal issue following command (this allows ajax requests)
`/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --ignore-certificate-errors &> /dev/null &`

- Admin portal - https://admin
