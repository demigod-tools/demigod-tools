<?php

namespace Pantheon\DemigodTools\Commands;

use Pantheon\DemigodTools\Utility\Crypt;
use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\TerminusHello\Model\Greeter;

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

    $this->copyFrameworkFiles( $this->getFramework(), $site_name, $base_dir, $clone_dir );
    $this->processGitIgnore($clone_dir);

    exec('direnv allow');
    if (php_uname("s") == "Darwin") {
      exec('brew bundle install');
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
      list( $site_name, $base_dir, $clone_dir ) = $args;
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
     * @return string The site framework, pulled from site:info.
     */
    public function getFramework() : string {
      $output = [];
      exec( 'terminus site:info | grep Framework | xargs', $output );

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
