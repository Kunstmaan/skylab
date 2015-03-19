backup
------

* Description: Run backup on all or one Skylab projects
* Usage: `backup [--hideLogo] [--no-interactive] [--quick] [project]`
* Aliases: <none>

The <info>backup</info> command will dump all your databases and create a tarball of one or all projects.

<info>php skylab.phar backup</info>                         # Will backup all projects
<info>php skylab.phar backup myproject</info>               # Will backup the myproject project
<info>php skylab.phar backup myproject --quick</info>       # Will backup the myproject project, but not create the tar file.


### Arguments:

**project:**

* Name: project
* Is required: no
* Is array: no
* Description: If set, the task will only backup the project named
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

**quick:**

* Name: `--quick`
* Shortcut: <none>
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: If set, no tar.gz file will be created, only the preBackup and postBackup hooks will be executed.
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
