# Skylab

Skylab is a 100% backwards compatible PHP port of the Python based hosting scripts used at Kunstmaan. By using the Cilex,
Symfony Components and lot's of good code samples from the Composer project we hope to create an application where
everyone in our organisation can work with, and if issues or new needed features should arise, be able to work on.

Kunstmaan wouldn't be Kunstmaan is we didn't open-source this, so here it is. [MIT licensed](./LICENSE).

## Installation

To install Skylab, run this command:

```
$ curl -sSL https://raw.github.com/Kunstmaan/skylab/master/installer | php
```

This will just check a few PHP settings and then download skylab.phar to your working directory. This file is the Skylab
binary. It is a PHAR (PHP archive), which is an archive format for PHP which can be run on the command line, amongst
other things.

You can install Skylab to a specific directory by using the --install-dir option and providing a target directory (it
can be an absolute or relative path):

```
$ curl -sSL https://raw.github.com/Kunstmaan/skylab/master/installer | php -- --install-dir=/bin
```

You can also install Skylab globally by running:

```
$ curl -sSL https://raw.github.com/Kunstmaan/skylab/master/installer | php
$ mv skylab.phar /usr/local/bin/skylab
```

## Configuration

You can override the [default configuration](./config.yml) by creating a file /etc/skylab.yml and give it a secure chmod
```
sudo chmod 700 /etc/skylab.yml
```

For example if you installed skylab on a developer OSX machine (be sure to replace the mysql password, postgresql username and password):
```
users:
    wwwuser:        apache

webserver:
    engine: apache

mysql:
    user:     root
    password: XXXXXXXXX

postgresql:
    user:     <username>
    password: XXXXXXXXX

debug: true
develmode: true
```

Or on an ubuntu system  (be sure to replace the mysql password and postgresql password):
```
users:
    wwwuser: www-data

webserver:
    engine: apache

mysql:
    user:     root
    password: XXXXXXXXX

postgresql:
    user:     postgres
    password: XXXXXXXXX

debug: true
develmode: true
```
## Commands

### NewProjectCommand

Usage: ```php skylab.phar new [--hideLogo] [--no-interactive] [name]```

The ```new``` command creates a new project. It will setup the directory structure and apply the "base" skeleton
which is responsible for setting up users, permissions and ownership.

```php skylab.phar new```
```php skylab.phar new testproject```

Full details at [doc/NewProjectCommand.md](doc/NewProjectCommand.md)

### FetchCommand

Usage: ```php skylab.phar fetch [--hideLogo] [--no-interactive] [-l|--location[="..."]] [--no-database] [project] [host]```

The ```fetch``` command fetches a Skylab project from a server and puts it in the right locations on your computer. It
will also drop the databases, so be very careful if you want to use this on a production server to do a migration.

```php skylab.phar fetch```                         # Will ask for a project and server to fetch it from
```php skylab.phar fetch testproject server1```     # Will fetch the testproject from server1

Full details at [doc/FetchCommand.md](doc/FetchCommand.md)

### ShareCommand

Usage: ```php skylab.phar share [--hideLogo] [--no-interactive]```

The ```share``` command show a table of all your locally installed projects together with the xip.io url.

```php skylab.phar share```                         # Will show the xip.io table

Full details at [doc/ShareCommand.md](doc/ShareCommand.md)

### SetPermissionsCommand

Usage: ```php skylab.phar permissions [--hideLogo] [--no-interactive] name```

The ```permissions``` command will fix the permissions of a project.

```php skylab.phar permissions testproject```

Full details at [doc/SetPermissionsCommand.md](doc/SetPermissionsCommand.md)

### MaintenanceCommand

Usage: ```php skylab.phar maintenance [--hideLogo] [--no-interactive] [--quick]```

The ```maintenance``` command will run the maintenance commands of all skeletons on a project. Most notably, it
will create the apache config files and make sure the the databases are available.

```php skylab.phar maintenance```

Full details at [doc/MaintenanceCommand.md](doc/MaintenanceCommand.md)

### BackupCommand

Usage: ```php skylab.phar backup [--hideLogo] [--no-interactive] [--quick] [project]```

The ```backup``` command will dump all your databases and create a tarball of one or all projects.

```php skylab.phar backup```                         # Will backup all projects
```php skylab.phar backup myproject```               # Will backup the myproject project
```php skylab.phar backup myproject --quick```       # Will backup the myproject project, but not create the tar file.

Full details at [doc/BackupCommand.md](doc/BackupCommand.md)

### RemoveProjectCommand

Usage: ```php skylab.phar remove [--hideLogo] [--no-interactive] [--force] [name]```

The ```remove``` command will remove the project after creating a backup first.

```php skylab.phar remove testproject```                         # Will remove the testproject project
```php skylab.phar remove testproject --force```                 # Will do the same, but don't ask you if you are sure.

Full details at [doc/RemoveProjectCommand.md](doc/RemoveProjectCommand.md)

### ApplySkeletonCommand

Usage: ```php skylab.phar apply [--hideLogo] [--no-interactive] [-l|--list] [project] [skeleton]```

The ```apply``` command applies a skeleton, and all it's dependencies to a project. It will run the "create"
method in the skeleton to setup all the requirements for that skeleton.

```php skylab.phar apply -l```                      # Lists all available skeletons
```php skylab.phar apply```                         # Will ask for a project and skeleton to apply
```php skylab.phar apply testproject anacron```     # Will apply the anacron skeleton to testproject

Full details at [doc/ApplySkeletonCommand.md](doc/ApplySkeletonCommand.md)

### SelfUpdateCommand

Usage: ```php skylab.phar self-update [--hideLogo] [--no-interactive]```

The ```self-update``` command will check if there is an updated skylab.phar released and updates if it is.

```php skylab.phar self-update```

Full details at [doc/SelfUpdateCommand.md](doc/SelfUpdateCommand.md)


## Compiling a new version

1. First, make sure everything works and the Travis tests are green [![Build Status](https://travis-ci.org/Kunstmaan/skylab.png?branch=master)](https://travis-ci.org/Kunstmaan/skylab)
1. Generate an updated changelog using ```git changelog``` from [git-extras](https://github.com/visionmedia/git-extras)
1. Commit this new changelog
1. Create a new release from the Github interface, add the new changelog part in the description and name the release for [the next brightst star in this list](http://en.wikipedia.org/wiki/List_of_brightest_stars)
1. Compile a new version ```./compile --version 0.1.2```
1. Add the new phar file to the release on GitHub

## Modifying the documentation

1. Most text is in ```gen-doc```, edit what you want there.
1. Everything in doc/ is generated, same goes for everything in README.md under Commands
1. Run ```./gen-doc > README.md``` to update the docs
1. Send a pull request

## Contributing

1. Fork Skylab
1. Do your thing, and send a Pull Request. But please make sure Travis is green and your code has been run through php-cs-fixer!


[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Kunstmaan/skylab/badges/quality-score.png?s=3d1f00bf9c2adbba818f274086db3ed4b2bcc4e2)](https://scrutinizer-ci.com/g/Kunstmaan/skylab/)


*Documentation generated on 2015-03-19 13:54:12*
