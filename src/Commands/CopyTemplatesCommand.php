<?php
/**
 * This variation on the Hello command shows how use the `@authenticated`
 * attribute to signal Terminus to require an authenticated session to
 * use this command.
 */

namespace Pantheon\DemigodTools\Commands;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\TerminusHello\Model\Greeter;

/**
 * Say hello to the user
 */
class CopyTemplatesCommand extends TerminusCommand
{
  /**
   * Copy Templates
   *
   * @command demigod:copy-templates
   * @aliases dgct
   * @param string $site_name
   *
   */
    public function copyTemplates(string $site_name)
    {
        $base_dir = dirname(dirname(__DIR__));
        $clone_dir = $_SERVER['HOME'] . '/pantheon-local-copies/' . $site_name;
        if (!is_dir($clone_dir)) {
          throw new \Exception("TODO: clone this automatically if it doesn't exist.");
        }
        // search for "pv, mysql-client" install if necessary
        foreach ([
                   $clone_dir . '/web/sites/default',
                   $clone_dir . '/db',
                   $clone_dir . '/logs',

                 ] as $directory) {
          if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
          }
          touch($directory . "/.gitkeep");
        }
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

                    case '.envrc':
                        $contents = file_get_contents($iterator->current()->getRealPath());
                        $contents = str_replace('**PROJECT_NAME**', $site_name, $contents);
                        $contents = str_replace('**PROJECT_PATH**', $clone_dir, $contents);
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
        chdir($clone_dir);
        exec('echo ".idea\n.envrc\nlogs/*\ndb/*\n.DS_Store" >> .gitignore ');
        exec('direnv allow');
        if (php_uname("s") == "Darwin") {
          exec('brew bundle install');
        }
    }
}
