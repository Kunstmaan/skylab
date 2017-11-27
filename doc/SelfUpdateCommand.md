self-update
-----------

* Description: Update skylab.phar to most recent stable, pre-release or development build.
* Usage:

  * `self-update [--hideLogo] [--no-interactive] [-d|--dev] [-N|--non-dev] [-p|--pre] [-s|--stable] [-r|--rollback] [-c|--check]`

The <info>self-update</info> command will check if there is an updated skylab.phar released and updates if it is.

<info>php skylab.phar self-update</info>


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

**dev:**

* Name: `--dev`
* Shortcut: `-d`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Update to most recent development build of Skylab.
* Default: `false`

**non-dev:**

* Name: `--non-dev`
* Shortcut: `-N`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Update to most recent non-development (alpha/beta/stable) build of Skylab tagged on Github.
* Default: `false`

**pre:**

* Name: `--pre`
* Shortcut: `-p`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Update to most recent pre-release version of Skylab (alpha/beta/rc) tagged on Github.
* Default: `false`

**stable:**

* Name: `--stable`
* Shortcut: `-s`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Update to most recent stable version of Skylab tagged on Github.
* Default: `false`

**rollback:**

* Name: `--rollback`
* Shortcut: `-r`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Rollback to previous version of Skylab if available on filesystem.
* Default: `false`

**check:**

* Name: `--check`
* Shortcut: `-c`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Checks what updates are available across all possible stability tracks.
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

*Documentation generated on 2017-11-23 11:37:31*
