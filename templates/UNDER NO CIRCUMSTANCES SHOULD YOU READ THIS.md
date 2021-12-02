# Docker Local Hosting for Development

## Given a pantheon site based on the Drupal 9 upstream:

### Install using terminus 3:

1. `terminus self:plugin:install demigod-tools/demigod-tools`

2. `terminus demigod:copy-templates {SITENAME}`

3.  `robo docker:up && robo site:install && robo site:login`

### Manual install

1. `brew package install `

2. `env | grep shell` will tell you which shell you're using

3. Follow the instructions to enable DIRENV for your shell: https://direnv.net/docs/hook.html

4. composer global require consolidation/robo

   ```
   project root
   |
   |    => Brewfile <=
   |    => .envrc <=
   |       composer.json
   |       composer.lock
   |    => docker-compose.yml <=
   |       pantheon.yml
   |    => RoboFile.php <=
   |------ web
           |------ core
           |------ modules
           |------ sites
                   |-----default
                   |     |----- => settings.local.php <=
                   |
                   |-----themes

   ```

5. `direnv allow`

6. `robo docker:up`

7. You now have a development server running locally:

   #### http://localhost:8080
   [Drupal Access php/nginx](http://localhost:8080)

   #### http://localhost:8983/solr
   [Solr Admin Panel](http://localhost:8983/solr)

   #### http://localhost:9000
   [Zookeeper UI](http://localhost:9000)


   ```bash

   # use terminus to pull the database from {ENV} and install
   robo site:pull-database {ENV}

   # use terminus to pull the files from {ENV} and install
   robo site:pull-files {ENV}

   # install the umami profile
   robo site:install

   # generate a one-time login for the installed site
   robo site:login

   # executes drush command
   robo drush {DRUSH COMMAND}

   ```
