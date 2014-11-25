new
---

* Description: Create a new Skylab project
* Usage: `new [--hideLogo] [--no-interactive] [name]`
* Aliases: <none>

The <info>new</info> command creates a new project. It will setup the directory structure and apply the "base" skeleton
which is responsible for setting up users, permissions and ownership.

<info>php skylab.phar new</info>
<info>php skylab.phar new testproject</info>


### Arguments:

**name:**

* Name: name
* Is required: no
* Is array: no
* Description: The name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive
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
* Description: Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug.
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

*Documentation generated on 2014-11-25 10:52:48*
