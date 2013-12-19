# Skylab

Skylab is a 100% backwards compatible PHP port of the Python based hosting scripts used at Kunstmaan. By using the Cilex,
Symfony Components and lot's of good code samples from the Composer project we hope to create an application where
everyone in our organisation can work with, and if issues or new needed features should arise, be able to work on.

Kunstmaan wouldn't be Kunstmaan is we didn't open-source this, so here it is. [MIT licensed](./LICENSE).

## Installation

### Skylab chef recipe

TO DO: factor out the generic code needed for Skylab from our big chef recipe

### Skylab application

TO DO: create an installer like composer

## Commands

### NewProjectCommand

Usage: ```php skylab.phar new [--hideLogo] [name]```

The ```new``` command creates a new project. It will setup the directory structure and apply the "base" skeleton
which is responsible for setting up users, permissions and ownership.

```php skylab.phar new```
```php skylab.phar new testproject```

Full details at [doc/NewProjectCommand.md](doc/NewProjectCommand.md)

### SetPermissionsCommand

Usage: ```php skylab.phar permissions [--hideLogo] name```


Full details at [doc/SetPermissionsCommand.md](doc/SetPermissionsCommand.md)

### MaintenanceCommand

Usage: ```php skylab.phar maintenance [--hideLogo]```


Full details at [doc/MaintenanceCommand.md](doc/MaintenanceCommand.md)

### BackupCommand

Usage: ```php skylab.phar backup [--quick] [--hideLogo] [project]```


Full details at [doc/BackupCommand.md](doc/BackupCommand.md)

### RemoveProjectCommand

Usage: ```php skylab.phar remove [--force] [--hideLogo] [name]```


Full details at [doc/RemoveProjectCommand.md](doc/RemoveProjectCommand.md)

### ApplySkeletonCommand

Usage: ```php skylab.phar apply [-l|--list] [--hideLogo] [project] [skeleton]```

The ```apply``` command applies a skeleton, and all it's dependencies to a project. It will run the "create"
method in the skeleton to setup all the requirements for that skeleton.

```php skylab.phar apply -l```                      # Lists all available skeletons
```php skylab.phar apply```                         # Will ask for a project and skeleton to apply
```php skylab.phar apply testproject anacron```     # Will apply the anacron skeleton to testproject

Full details at [doc/ApplySkeletonCommand.md](doc/ApplySkeletonCommand.md)

### SelfUpdateCommand

Usage: ```php skylab.phar self-update [--hideLogo]```


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


*Documentation generated on 2013-12-19 12:50:26*