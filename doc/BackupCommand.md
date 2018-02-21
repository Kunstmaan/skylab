backup
------

* Description: Run backup on all or one Skylab projects
* Usage:

  * `backup [--hideLogo] [--no-interactive] [--quick] [--] [<project>]`

The <info>backup</info> command will dump all your databases and create a tarball of one or all projects.

```
php skylab.phar backup  # will backup all projects
```                                     
```
php skylab.phar backup myproject  # will backup the myproject project
```                          
```
php skylab.phar backup myproject --quick # will backup the myproject project, but not create the tar file.
```
```
php skylab.phar backup myproject --quick --anonymize # will backup the myproject project, but not create the tar file, and anonymize the database with the edyan/neuralizer package.
```       

For the anonymize to work, you will have to create a anon.yml file in your .skylab folder. Example:

Possible options are:
- pre_queries: will run before the anonymization
- post_queries: will run after the anonymization

```
guesser_version: 1.0.0b
locale: nl_BE
entities:
    kuma_form_submission_fields:
        cols:
            efsf_value: { method: safeEmail }
    foo_contact_addresss:
        cols:
            company: { method: company }
            vat: { method: vat }
            street: { method: streetName }
            city: { method: city }
            country: { method: countryCode }
    foo_users:
        cols:
            username: { method: safeEmail }
            username_canonical: { method: safeEmail }
            email: { method: safeEmail }
            email_canonical: { method: safeEmail }
            first_name: { method: firstName }
            last_name: { method: lastName }
post_queries:
    - "Update foo_users SET email_canonical = email"
    - "Update foo_users SET username = email"
    - "Update foo_users SET username_canonical = email"

```

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

**anonymize:**

* Name: `--anonymize`
* Shortcut: <none>
* Accept value: no
* Is value required: no
* Is multiple: no
* Description: If set, the database will be anonymized when exporting. This uses the edyan/neuralizer package.
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

*Documentation generated on 2017-12-19 12:42:37*
