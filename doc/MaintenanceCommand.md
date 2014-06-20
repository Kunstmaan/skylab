maintenance
-----------

* Description: Run maintenance on all Skylab projects
* Usage: `maintenance [--hideLogo] [--no-interactive]`
* Aliases: <none>

The <info>maintenance</info> command will run the maintenance commands of all skeletons on a project. Most notably, it
will create the apache config files and make sure the the databases are available.

<info>php skylab.phar maintenance</info>


### Options:

**hideLogo:**

* Name: `--hideLogo`
* Shortcut: <none>
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: If set, no logo or statistics will be shown
* Default: `false`

**no-interactive:**

* Name: `--no-interactive`
* Shortcut: <none>
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: If set, no questions will be asked
* Default: `false`

**help:**

* Name: `--help`
* Shortcut: `-h`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Display this help message.
* Default: `false`

**quiet:**

* Name: `--quiet`
* Shortcut: `-q`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Do not output any message.
* Default: `false`

**verbose:**

* Name: `--verbose`
* Shortcut: `-v|-vv|-vvv`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
* Default: `false`

**version:**

* Name: `--version`
* Shortcut: `-V`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Display this application version.
* Default: `false`

**ansi:**

* Name: `--ansi`
* Shortcut: <none>
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Force ANSI output.
* Default: `false`

**no-ansi:**

* Name: `--no-ansi`
* Shortcut: <none>
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Disable ANSI output.
* Default: `false`

**no-interaction:**

* Name: `--no-interaction`
* Shortcut: `-n`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Do not ask any interactive question.
* Default: `false`

*Documentation generated on 2014-06-20 16:04:39*
