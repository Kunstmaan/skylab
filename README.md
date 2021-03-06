# Skylab

Skylab is a 100% backwards compatible PHP port of the Python based hosting scripts used at Kunstmaan. By using the Cilex,
Symfony Components and lots of good code samples from the Composer project we hope to create an application that
everyone in our organisation can work with, and if issues or new needed features should arise, be able to work on.

Kunstmaan wouldn't be Kunstmaan if we didn't open-source this, so here it is. [MIT licensed](./LICENSE).

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

For example if you installed skylab on a developer OSX machine:
```
users:
    wwwuser:        apache

webserver:
    engine: apache
    hostmachine: XXXXX.kunstmaan.be

mysql:
    user:     root
    password: XXXXXXXXX

postgresql:
    user:     postgres
    password: XXXXXXXXX

debug: true
develmode: true
```

Or on an ubuntu system:
```
users:
    wwwuser: www-data

webserver:
    engine: apache
    hostmachine: XXXXX.kunstmaan.be

mysql:
    user:     root
    password: XXXXXXXXX

postgresql:
    user:     postgres
    password: XXXXXXXXX

debug: true
develmode: true
```
## Special Skeletons
### SSL skeleton
The ```ssl``` skeleton can be used to configure the SSL configurations in apache via the config.xml

You start by adding ```<item value="ssl"/>``` to the skeletons in config.xml. Then you need to add ```<var name="project.sslConfig">``` to the config.xml to configure the SSL configuration.

You can specify different SSL configuration that has to be used per environment. The syntax for each environment is the same
```
<var name="{environment}">
  <dir value="{the location of the ssl files (cert, key, ca)}"/>
  <certFile value="{the name of the cert file}"/>
  <certKeyFile value="{the name of the key file}"/>
  <caCertFile value="{the name of the ca cert file}"/>
</var>
```

Example (we assume three environments (dev,staging,prod)).
```
<var name="project.sslConfig">
    <var name="dev">
      <dir value="/home/myproject/ssl/dev/"/>
      <certFile value="myproject_dev_ssl.crt"/>
      <certKeyFile value="myproject_dev_ssl.key"/>
      <caCertFile value="myproject_dev_ssl.ca-bundle"/>
    </var>
    <var name="staging">
      <dir value="/home/myproject/ssl/staging/"/>
      <certFile value="myproject_staging_ssl.crt"/>
      <certKeyFile value="myproject_staging_ssl.key"/>
      <caCertFile value="myproject_staging_ssl.ca-bundle"/>
    </var>
    <var name="prod">
      <dir value="/home/myproject/ssl/prod/"/>
      <certFile value="myproject_prod_ssl.crt"/>
      <certKeyFile value="myproject_prod_ssl.key"/>
      <caCertFile value="myproject_prod_ssl.ca-bundle"/>
    </var>
  </var>
```

Which ssl configuration will be used depends on the value ```env``` in you skylab.yml file. Locally you should have ```env: dev``` in you skylab.yml configuration file. If you do then when running maintenance it will add the dev SSL config in apache.

###Letsencrypt skeleton
The ```letsencrypt``` skeleton can be used to generate ssl certificates for your site using the Let's Encrypt service.

To enable the use of letsenecrypt you have to add ```<item value="letsenecrypt"/>``` to the config.xml.

The skeleton will create a ssl certificate for the urls ```project.url``` and all the aliases in ```project.aliases```.

IMPORTANT NOTES:
1. The letsencrypt skeleton will run the command only on a production server.
1. Make sure the urls resolve to the IP where the command will run, otherwise it will fail.
1. You can use the ```ssl``` and ```letsencrypt``` skeletong together, BUT when enabling both the skeletons you must remove the prod ssl config. If the prod ssl config is available it will use that config instead of running letsencrypt.
1. The letsencrypt skeleton also creates a cronjob to renew the certs (e.g. 0 0 * * 0 letsencrypt --apache -n certonly -d myproject.com)

## Commands

### NewProjectCommand

Usage: ```php skylab.phar new [--hideLogo] [--no-interactive] [--] [<name>]```

The ```new``` command creates a new project. It will setup the directory structure and apply the "base" skeleton
which is responsible for setting up users, permissions and ownership.

```php skylab.phar new```
```php skylab.phar new testproject```

Full details at [doc/NewProjectCommand.md](doc/NewProjectCommand.md)

### FetchCommand

Usage: ```php skylab.phar fetch [--hideLogo] [--no-interactive] [-l|--location [LOCATION]] [--no-database] [--] [<project>] [<host>]```

The ```fetch``` command fetches a Skylab project from a server and puts it in the right locations on your computer. It
will also drop the databases, so be very careful if you want to use this on a production server to do a migration.

```php skylab.phar fetch```                         # Will ask for a project and server to fetch it from
```php skylab.phar fetch testproject server1```     # Will fetch the testproject from server1

Full details at [doc/FetchCommand.md](doc/FetchCommand.md)

### ShareCommand

Usage: ```php skylab.phar share [--hideLogo] [--no-interactive]```

The ```share``` command shows a table of all your locally installed projects together with the xip.io url.

```php skylab.phar share```                         # Will show the xip.io table

Full details at [doc/ShareCommand.md](doc/ShareCommand.md)

### SetPermissionsCommand

Usage: ```php skylab.phar permissions [--hideLogo] [--no-interactive] [--] <name>```

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

Usage: ```php skylab.phar backup [--hideLogo] [--no-interactive] [--quick] [--] [<project>]```

The ```backup``` command will dump all your databases and create a tarball of one or all projects.

```
php skylab.phar backup  # Will backup all projects
```                                     
```
php skylab.phar backup myproject  # Will backup the myproject project
```                          
```
php skylab.phar backup myproject --quick # Will backup the myproject project, but not create the tar file.
```
```
php skylab.phar backup myproject --quick --anonymize # Will backup the myproject project, but not create the tar file, and anonymize the database with the edyan/neuralizer package.
```       

Full details at [doc/BackupCommand.md](doc/BackupCommand.md)

### RemoveProjectCommand

Usage: ```php skylab.phar remove [--hideLogo] [--no-interactive] [--force] [--no-backup] [--] [<name>]```

The ```remove``` command will remove the project after creating a backup first.

```php skylab.phar remove testproject```                         # Will remove the testproject project
```php skylab.phar remove testproject --force```                 # Will do the same, but don't ask you if you are sure.

Full details at [doc/RemoveProjectCommand.md](doc/RemoveProjectCommand.md)

### ApplySkeletonCommand

Usage: ```php skylab.phar apply [--hideLogo] [--no-interactive] [-l|--list] [--] [<project>] [<skeleton>]```

The ```apply``` command applies a skeleton, and all it's dependencies to a project. It will run the "create"
method in the skeleton to setup all the requirements for that skeleton.

```php skylab.phar apply -l```                      # Lists all available skeletons
```php skylab.phar apply```                         # Will ask for a project and skeleton to apply
```php skylab.phar apply testproject anacron```     # Will apply the anacron skeleton to testproject

Full details at [doc/ApplySkeletonCommand.md](doc/ApplySkeletonCommand.md)

### SelfUpdateCommand

Usage: ```php skylab.phar self-update [--hideLogo] [--no-interactive] [-d|--dev] [-N|--non-dev] [-p|--pre] [-s|--stable] [-r|--rollback] [-c|--check]```

The ```self-update``` command will check if there is an updated skylab.phar released and updates if it is.

```php skylab.phar self-update```

Full details at [doc/SelfUpdateCommand.md](doc/SelfUpdateCommand.md)


## Compiling a new version

1. First, make sure everything works and the Travis tests are green [![Build Status](https://travis-ci.org/Kunstmaan/skylab.png?branch=master)](https://travis-ci.org/Kunstmaan/skylab)
1. Generate an updated changelog using ```git changelog``` from [git-extras](https://github.com/visionmedia/git-extras)
1. Commit this new changelog
1. Create a new release from the Github interface, add the new changelog part in the description and name the release for [the next brightst star in this list](http://en.wikipedia.org/wiki/List_of_brightest_stars)
1. download [box.par](https://github.com/box-project/box2) to create the new version
1. Build a new version using box.phar box.phar build -v. Note: make sure you have pulled in the latest tag!!
1. Add the new phar file to the release on GitHub
1. Update [packagist](https://packagist.org/packages/kunstmaan/skylab)

## Modifying the documentation

1. Most text is in ```gen-doc```, edit what you want there.
1. Everything in doc/ is generated, same goes for everything in README.md under Commands
1. Run ```./gen-doc > README.md``` to update the docs
1. Send a pull request

## Contributing

1. Fork Skylab
1. Do your thing, and send a Pull Request. But please make sure Travis is green and your code has been run through php-cs-fixer!


[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Kunstmaan/skylab/badges/quality-score.png?s=3d1f00bf9c2adbba818f274086db3ed4b2bcc4e2)](https://scrutinizer-ci.com/g/Kunstmaan/skylab/)


*Documentation generated on 2017-12-19 12:42:37*
