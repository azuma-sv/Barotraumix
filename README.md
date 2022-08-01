# Barotraumix
Portal for Barotrauma. Mod making tool and wiki.

## How to set up local environment with Barotraumix in it?
Prerequisites:
- [Docker](https://docs.docker.com/get-docker/)
- [Lando](https://docs.lando.dev/getting-started/installation.html)
- [GIT](https://git-scm.com/downloads)
  Follow the links above and install Docker, Lando and GIT.

1. Set up new SSH key for your repo.
```
  cd ~/.ssh
  ssh-keygen -t rsa -b 4096 -C "github@email.example"
```
I recommend to store it as separate file. Name it like: "barotraumix"/"barotraumix.pub".

2. Prepare repo for usage.
```
   cd /path/to/your/projects/folder
   mkdir barotraumix
   cd barotraumix
   git init
   git config core.sshCommand 'ssh -i C:/Users/<Your_Username>/.ssh/barotraumix'
   git remote add origin git@github.com:azuma-sv/Barotraumix.git
   git pull origin main
```

3. Prepare project for usage:
```
  # Run containers.
  lando start
  # Install dependencies.
  lando composer install
  # List information about this app
  lando info
```

4. Prepare database for usage.
   Copy the file "sites/default/default.settings.php" to "sites/default/settings.php".
   Uncomment following lines in the end of your copied file:
```
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
```

Put content below into created file (it is ignored by GIT): "sites/default/settings.local.php"
```
<?php
// Trusted localhost.
$settings['trusted_host_patterns'] = [
  '^localhost$',
  '127\.0\.0\.1',
  '^' . getenv('LANDO_APP_NAME') . '\.lndo\.site$'
];
// Directory to store configurations.
$settings['config_sync_directory'] = '../config/sync';
// Path to public and private files.
$settings['file_public_path'] = 'sites/default/files';
$settings['file_private_path'] = '../private';
// Path to barotrauma source files.
if (!empty(getenv('BAROTRAUMA_SOURCE_FILES'))) {
  $settings['file_barotrauma_path'] = getenv('BAROTRAUMA_SOURCE_FILES');
}
// Disable JS and CSS aggregation.
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
// Turn off render cache, page cache and dynamic page cache.
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';
// Standard credentials for Lando.
$databases['default']['default'] = array (
  'database' => 'drupal9',
  'username' => 'drupal9',
  'password' => 'drupal9',
  'prefix' => '',
  'host' => 'database',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
```

5. Disable TWIG cache.
   Create the file "sites/default/services.yml" with following content:
```
# Local development services.
parameters:
  http.response.debug_cacheability_headers: true
  twig.config:
    debug: true
    auto_reload: true
    cache: false
services:
  cache.backend.null:
    class: Drupal\Core\Cache\NullBackendFactory
```

5. Install the site:
```
  # You should change username and password to your own.
  lando drush site:install --db-url=mysql://drupal9:drupal9@database/drupal9 -y --account-name=admin --account-pass=PASSWORD --site-name="Barotraumix"
  # Some day I will remove those "hacks". But at current moment we need to run them.
  lando drush cset system.site uuid 0cbc4e56-2d16-4540-8928-512ad7e1e874 -y
  lando drush php-eval "\Drupal::entityTypeManager()->getStorage('shortcut_set')->load('default')->delete();"
  # Import our configurations.
  lando drush cim -y
```

6. Next step:
   @todo: More steps will follow later (installation of Barotrauma with SteamCMD).



## Personal reminders.
@todo: I need to find out a way to remove unnecessary README files from project.

[Purge docker and Lando](https://gist.github.com/labboy0276/4406db072f9ed3bf3641f57c1d902027)



## Info on how to export things as default website content.
Every file name (without extension) should be added into info file of barotraumix module.

Application type:
lando drush dcer taxonomy_term 1 --folder=modules/custom/barotraumix/content
lando drush dcer taxonomy_term 2 --folder=modules/custom/barotraumix/content

Asset type:
lando drush dcer taxonomy_term 3 --folder=modules/custom/barotraumix/content
lando drush dcer taxonomy_term 4 --folder=modules/custom/barotraumix/content
