<?php

namespace Pantheon\DemigodTools\Commands;

use Pantheon\DemigodTools\Utility\Crypt;
use Pantheon\Terminus\Commands\TerminusCommand;

/**
 * Say hello to the user
 */
class CopyTemplatesCommand extends TerminusCommand {

  /**
   * @var int[]
   */
  protected static $toIgnore = [
    ".idea" => 1,
    ".envrc" => 1,
    "logs/*" => 1,
    "db/*" => 1,
    ".DS_Store" => 1,
    "Brewfile.loc*" => 1,
  ];

  /**
   * Copy Templates
   *
   * @command demigod:copy-templates
   * @aliases dgct
   *
   * @param string $site_name
   *
   * @throws \Exception
   */
  public function copyTemplates(string $site_name) {
    $base_dir = dirname(__DIR__, 2);
    $clone_dir = $_SERVER['HOME'] . '/pantheon-local-copies/' . $site_name;
    if (!is_dir($clone_dir)) {
      throw new \Exception("TODO: clone this automatically if it doesn't exist.");
    }
    chdir($clone_dir);
    foreach ([
               'web/sites/default/files/translations',
               'web/sites/default/temp',
               'web/sites/default/private',
               'db',
               'logs',
             ] as $directory) {
      if (!is_dir($directory)) {
        mkdir($clone_dir . "/" . $directory, 0777, TRUE);
      }
      touch($clone_dir . "/" . $directory . "/.gitkeep");
    }
    exec("git add -f db/.gitkeep logs/.gitkeep");

    // We only have two types of templates -- drupal or wordpress. We might get any of a bunch of different types of frameworks back that we need to map to those two template types. Start by identifying some "known" allowed frameworks. This list may expand to include more frameworks.
    $allowed_frameworks = [ 'wordpress', 'wordpress_network', 'drupal', 'drupal8' ];
    // Identify the framework.
    $framework = $this->getFramework( $site_name );
    // If the framework isn't in the allowed_frameworks list, bail.
    if ( ! in_array( $framework, $allowed_frameworks ) ) {
      throw new \Exception( $framework );
    }
    // Determine if the framework is WP or Drupal.
    $framework = false === stripos( $framework, 'wordpress' ) ? 'drupal' : 'wordpress';
    $this->copyFrameworkFiles( $framework, $site_name, $base_dir, $clone_dir );
    $this->processGitIgnore($clone_dir);

    exec('direnv allow');
    if (php_uname("s") == "Darwin") {
      exec('brew bundle install');
    }
    echo (file_get_contents($base_dir . "/docs/demigod.txt"));
  }

  /**
   * Copy the template files based on the framework.
   *
   * @param string $framework The CMS framework identified by getFramework().
   * @param array ...$args Array of arguments required for copying files.
   *              $site_name The site name that was called into copyTemplates.
   *              $base_dir This plugin's base directory.
   *              $clone_dir The directory the site was cloned into.
   */
  private function copyFrameworkFiles( string $framework, ...$args ) {
    [ $site_name, $base_dir, $clone_dir ] = $args;
    $iterator = new \DirectoryIterator("$base_dir/templates/$framework");
    for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
        if (is_file($iterator->current()->getRealPath())) {
          switch ($iterator->current()->getFilename()) {
            case 'settings.local.php':
              copy(
                  $iterator->current()->getRealPath(),
                  $clone_dir . '/web/sites/default/settings.local.php'
              );
              break;

            case '.envrc':
              $contents = file_get_contents($iterator->current()->getRealPath());
              $contents = str_replace('**PROJECT_NAME**', $site_name, $contents);
              $contents = str_replace('**PROJECT_PATH**', $clone_dir, $contents);
              $contents = str_replace(
                '**HASH_SALT**',
                Crypt::randomBytesBase64(55),
                $contents
              );
              file_put_contents($clone_dir . '/' . $iterator->current()->getFilename(), $contents);
              break;

            default:
              copy(
                  $iterator->current()->getRealPath(),
                  $clone_dir . '/' . $iterator->current()->getFilename()
              );
          }
        }
    }
  }

  /**
   * Get the site framework.
   *
   * @command demigod:get-framework
   *
   * @param string $site_name The site name to query.
   *
   * @return string The site framework, pulled from site:info.
   */
  public function getFramework( string $site_name ) : string {
    $output = [];
    exec( "terminus site:info $site_name --format=json | jq -r .framework", $output );

    // If site:info didn't give us the output we expected, bail early.
    if ( empty( $output ) ) {
      return '❓ Could not determine site framework.';
    }

    $framework = str_replace( 'Framework ', '', $output[0] );

    return $framework;
  }

  /**
   * Make sure the ignores are in the .gitignore preventing duplicates.
   *
   * @param $directory
   */
  protected function processGitIgnore($directory) {
    $contents = explode(PHP_EOL, file_get_contents($directory . "/.gitignore"));
    $contents = array_combine($contents, array_fill(0, count($contents), 1));
    $contents = array_merge($contents, self::$toIgnore);
    file_put_contents($directory . "/.gitignore", implode(PHP_EOL, array_keys($contents)));
  }

}
