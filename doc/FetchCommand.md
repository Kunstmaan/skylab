fetch
-----

* Description: Fetches a project from a production server
* Usage: `fetch [--hideLogo] [--no-interactive] [-l|--location[="..."]] [--no-database] [project] [host]`
* Aliases: <none>

The <info>fetch</info> command fetches a Skylab project from a server and puts it in the right locations on your computer. It
will also drop the databases, so be very careful if you want to use this on a production server to do a migration.

<info>php skylab.phar fetch</info>                         # Will ask for a project and server to fetch it from
<info>php skylab.phar fetch testproject server1</info>     # Will fetch the testproject from server1


### Arguments:

**project:**

* Name: project
* Is required: no
* Is array: no
* Description: The name of the Skylab project
* Default: `NULL`

**host:**

* Name: host
* Is required: no
* Is array: no
* Description: The hostname of the server to fetch from
* Default: `NULL`

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

**location:**

* Name: `--location`
* Shortcut: `-l`
* Accept value: yes
* Is value required: no
* Is multiple: no
* Description: Override the target location
* Default: `NULL`

**no-database:**

* Name: `--no-database`
* Shortcut: <none>
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Don't delete the local database
* Default: `false`

**help:**

* Name: `--help`
* Shortcut: `-h`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Display this help message
* Default: `false`

**quiet:**

* Name: `--quiet`
* Shortcut: `-q`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Do not output any message
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
* Description: Display this application version
* Default: `false`

**ansi:**

* Name: `--ansi`
* Shortcut: <none>
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Force ANSI output
* Default: `false`

**no-ansi:**

* Name: `--no-ansi`
* Shortcut: <none>
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Disable ANSI output
* Default: `false`

**no-interaction:**

* Name: `--no-interaction`
* Shortcut: `-n`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Do not ask any interactive question
* Default: `false`

*Documentation generated on 2015-03-19 13:54:12*
