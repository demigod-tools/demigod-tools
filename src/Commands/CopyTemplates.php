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
class CopyTemplates extends TerminusCommand
{
    /**
     * Copy Templates
     *
     * @command demigod-tools:copy-templates
     * @aliases dtct
     *
     */
    public function copyTemplates(string $site_name)
    {
      print_r(get_defined_vars());
    }

}
