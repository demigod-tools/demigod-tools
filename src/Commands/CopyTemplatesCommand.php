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
    $iterator = new \DirectoryIterator($base_dir . '/templates');
    for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
      if (is_file($iterator->current()->getRealPath())) {
        switch ($iterator->current()->getFilename()) {
          case 'settings.local.php':
            copy(
              $iterator->current()->getRealPath(),
              $clone_dir . '/web/sites/default/settings.local.php'
            );
            break;

          case '.envrc.dist':
            $contents = file_get_contents($iterator->current()->getRealPath());
            $contents = str_replace('**PROJECT_NAME**', $site_name, $contents);
            $contents = str_replace('**PROJECT_PATH**', $clone_dir, $contents);
            $contents = str_replace(
              '**HASH_SALT**',
              Crypt::randomBytesBase64(55),
              $contents
            );
            file_put_contents($clone_dir . '/' . $iterator->current()
                ->getFilename(), $contents);
            file_put_contents($clone_dir . '/' . ".envrc", $contents);
            file_put_contents($clone_dir . '/' . ".env.docker", 
                str_replace("export ", "", $contents)
            );
            break;

          default:
            copy(
              $iterator->current()->getRealPath(),
              $clone_dir . '/' . $iterator->current()->getFilename()
            );
        }
      }
    }
    $this->processGitIgnore($clone_dir);
    exec('direnv allow');
    if (php_uname("s") == "Darwin") {
      exec('brew bundle install');
    }
    echo (file_get_contents($base_dir . "/docs/demigod.txt"));
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
