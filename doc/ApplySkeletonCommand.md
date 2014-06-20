apply
-----

* Description: Apply a skeleton to a Skylab project
* Usage: `apply [--hideLogo] [--no-interactive] [-l|--list] [project] [skeleton]`
* Aliases: <none>

The <info>apply</info> command applies a skeleton, and all it's dependencies to a project. It will run the "create"
method in the skeleton to setup all the requirements for that skeleton.

<info>php skylab.phar apply -l</info>                      # Lists all available skeletons
<info>php skylab.phar apply</info>                         # Will ask for a project and skeleton to apply
<info>php skylab.phar apply testproject anacron</info>     # Will apply the anacron skeleton to testproject


### Arguments:

**project:**

* Name: project
* Is required: no
* Is array: no
* Description: The name of the kServer project
* Default: `NULL`

**skeleton:**

* Name: skeleton
* Is required: no
* Is array: no
* Description: The name of the skeleton
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

**list:**

* Name: `--list`
* Shortcut: `-l`
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: Lists all available skeletons
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
