
1.0.22 / 2015-11-27
==================

  * Fix: no permissions to create anacronjobs file

1.0.21 / 2015-11-26
==================

  * Fix: anacron files not created correctly when no anacronjobs are defined

1.0.20 / 2015-11-24
==================

  * Fix anacron error
  * Update dependencies

1.0.19 / 2015-11-24
==================

  * Add 'webserver' command to perform webserver operations with abstraction of the webserver engine
  * Merge remote-tracking branch 'upstream/master'
  * Fix: check if apache2 or nginx is installed before reloading webserver configuration (2)
  * Revert "Fix: check if apache2 or nginx is installed before reloading webserver configuration"
  * Fix: check if apache2 or nginx is installed before reloading webserver configuration
  * Merge pull request #101 from benoitgeerinck/master
  * change branch retrieval when execute is launched
  * Merge pull request #100 from benoitgeerinck/master
  * remove double slash in slack URL
  * add branch name in slack notification
  * enable interpretation of backslash escapes
  * add execution permissions on anacronjobs file
  * add timestamp to shared_package_url
  * Merge pull request #97 from jockri/fix-cache-issue
  * Fix cache issue that occurs after you upgrade several composer packages and deploy again

1.0.18 / 2015-10-02
==================

  * Add monitoring calls
  * Fix typo
  * Make sure the symfony cron tasks run with the correct environment
  * only symlink to web/uploads if the destination folder exists
  * only symlink to web/uploads if the destination folder exists
  * The check if postgres driver is available was wrong, it's pgsql instead of postgresql

1.0.17 / 2015-09-28
==================

  * So very long timeout
  * Update dependencies

1.0.16 / 2015-09-28
==================

  * Use custom bash if installed
  * ionice does not exist on OSX
  * Fix for specific projects
  * Fix for large projects
  * only change web/app.php file if it exists
  * only change asset version in config.yml if the file exists
  * don't run mysql if the parameters file does not contain database info
  * fix fetch fixer
  * Merge branch 'master' of github.com:Kunstmaan/skylab
  * fix fetch fixer
  * Merge pull request #89 from Kunstmaan/fix/cantdeploywithoutpostgresuser
  * check if postgres user exists
  * Fix the git repo fixer
  * Fix the git repo fixer
  * Merge pull request #87 from piotrbelina/master
  * Fixed OS X sed option

1.0.15 / 2015-09-18
==================

  * Only set first vhost for ssl if ssl is enabled
  * Block unknown ssl hosts
  * replace the "<path to>" entries in the anacrontab with the correct projectfolder
  * fixing git repo after fetching a new project
  * fix share command for linux, and show warning when develmode is false and xipio will not work

1.0.14 / 2015-09-14
==================

  * Remove globbing
  * Update newlines

1.0.12 / 2015-09-14
==================

  * Fix newlines
  * Depend on anacron

1.0.11 / 2015-09-05
==================

  * Don't cache to prevent permission errors
  * Fix backups
  * Show an error message if you messed up your configuration files
  * Don't throw an error when Github is unresponsive
  * Exclude .cache, fixes https://app.getsentry.com/smarties/skylab/group/80027759/
  * Fix
  * Use Pro features
  * Add a monitoring skeleton
  * Increase check speed
  * Use the main domainname and use SSL when needed
  * Fix error in remove
  * Factor out the check name
  * Fix error

1.0.10 / 2015-09-04
==================

  * Fixke
  * Use the server url
  * Tweak logging
  * Improve logging
  * Correct logging
  * Fix the enabled key
  * More debugging
  * Move to statuscake
  * Always depend on pingdom
  * Merge branch 'master' of https://github.com/Kunstmaan/skylab
  * Move "current" and filemode to the webserver skeleton
  * Merge pull request #86 from kimausloos/master
  * Text fixes
  * Set the build home
  * Run oro update from the project directory

1.0.9 / 2015-08-27
==================

 * Merge pull request #85 from diskwriter/patch-1
 * Fix syntax error

1.0.8 / 2015-08-27
==================

 * Fix for no skylab
 * Nicer precursor
 * Cleaner notifications
 * get the branch from git
 * add branch
 * Move to single branch building
 * Tweak text a bit
 * Change notifications
 * Ordering
 * Symlink test
 * Fallback for kDeploy servers
 * Quotes!
 * Another quote
 * Extra quote
 * Safer
 * Merge branch 'master' of https://github.com/Kunstmaan/skylab
 * Tweak the mv command
 * Merge pull request #83 from Devolicious/patch-1
 * Fix permissions for oro update
 * Merge pull request #81 from jockri/fix-https-proxypass
 * added X-Forwarded-Proto header so Symfony knows it needs to generate https url's
 * Refactor
 * Fixes
 * Tweaks
 * Add shared folders and drush
 * Check for a running php before clearing the cache
 * Fix chmod errors for drupal deploys
 * No skylab available
 * Empty drupal template
 * Don't skip the session table
 * Add an extra ORO command on the server
 * Fix find for darwin
 * Use composer show -i

1.0.7 / 2015-08-23
==================

  * Remove the debug class loader since it fails in phars on osx
  * Split in two commands
  * Fix find on OSX
  * SH uses different comparison
  * quoting
  * Improve cache clear
  * Better spork detection
  * Add oro template
  * force into bash
  * More sh comparison fixes
  * Fix sh comparison
  * Fix sh errors
  * Fix versioning
  * Correct the command
  * Do spork!

1.0.6 / 2015-08-22
==================

  * update deps
  * Fix
  * Add newline at the end
  * Fix escaping

1.0.5 / 2015-08-22
==================

  * Merge pull request #80 from Kunstmaan/deploy-yml
  * No SSL aliasses
  * Adding an Execute command to handle CI building from Jenkins and Slack. Also fix several bugs.
  * Don't try to queue the nightly cronjob if no cronjobs are defined

1.0.4 / 2015-08-12
==================

  * Fix missing quote
  * Make sure contab errors get mailed to cron@kunstmaan.be
  * Merge pull request #79 from Kunstmaan/features/wildcarddevalias
  * Use wildcard instead of www, to enable multi-domain urls

1.0.3 / 2015-08-11
==================

  * Handle Aliasses for SSL vhosts better

1.0.2 / 2015-08-10
==================

  * Add the SSL skeleton
  * Code cleanup
  * Don't fail on typo's
  * Don't log typo's
  * Add more Sentry infomation to make triaging errors more easy

1.0.1 / 2015-08-10
==================

  * Don't fail on unmigrated projects

1.0.0 / 2015-08-09
==================

  * Add mod_jk to the pre build steps
  * Add slack notifications for build errors
  * Fail hard on exception
  * Logging errrors should also send a message to sentry
  * Tweak travis
  * Remove sshuttle config file
  * Add a fixcron command
  * differentiate between local and remote project paths
  * Prefer *.local files in apache.d and config.xml.local
  * Capture unmigrated projects
  * Add as much debugging info as possible
  * Capture exceptions and send them to Sentry
  * add composer.lock to git
  * fix compiler should also include .pem files from vendors

0.1.4 / 2015-07-29
==================

 * Better apache php configuration
 * Better if structure

0.1.3 / 2015-07-28
==================

 * fix conf symlink after fetch
 * Enable sentry in developer mode

0.0.11 / 2015-03-30
==================

 * Disable sentry in developer mode
 * Add php7.0 to .travis.yml
 * Set APP_ENV variable to dev in apache if in development mode
 * Update config.yml
 * load tomcat xml file with sudo
 * create default symlink
 * create workers.properties file
 * Ignore missing smlkerberos skeleton

0.0.10 / 2015-03-19
==================

 * A new "skylab share" command for listing the xip.io urls
 * Added some safety checks on removing a project
 * Warning when project will not be in the hosts file
 * Fixes for when a skeleton is false
 * Use wildcard in xip.io aliases, no need to get the server ip address here
 * Don't talk about new versions when using the source version
 * Only use xip.io in develmode
 * No crons in develmode

0.0.9 / 2015-03-02
==================

 * ignore github api limit on each command exception for the self-update command, there we ask for a username/password when the api limit is reached
 * fix sudo for self-update
 * Merge branch 'master' of github.com:Kunstmaan/skylab
 * curl cache for getting the releases from github
 * changelog 0.0.8
 * Fix tomcat version

0.0.8 / 2015-03-02
==================

 * escape shell commands that are passed through sudo
 * Merge pull request #40 from krispypen/feature/xipio
 * possible to use xip.io
 * Added documentation for configuration files
 * ignore data/builds in syncing projects because of new ci deploy flow
 * use trysudo for getting /etc/skylab.yml file and fix reading template files in phar
 * possible to change the local auto hosts ip address
 * Merge pull request #38 from bakie/new_release_message
 * show new release message when available
 * added fetch documentation
 * Merge pull request #35 from kimausloos/master
 * No sudo for composer selfupdate
 * Update composer and apt-cache  before installing
 * Ignore solr skeleton
 * Add some options or the firelane databases
 * Move the vendoring back
 * Postgres needs a user
 * Also configure users in dev mode
 * Sometimes the user does not exist yet
 * Gracefully handle Apache 2.2 to 2.4 changes
 * Use current
 * Always go via current

0.0.7 / 2014-12-17
==================

 * Fix error with nginx ipv6 options
 * Speed up maintenance quick
 * Fixes for antares
 * Fix project creation
 * Don't fail if certain db drivers are not installed
 * Actually return
 * More errors
 * Fix warnings for strnagely configured applications
 * Fix compatibility with older nginx projects
 * Typo
 * Also add main url

0.0.6 / 2014-11-25
==================

  * Add completion via https://github.com/stecman/symfony-console-completion

0.0.5 / 2014-11-25
==================

  * More doc updates
  * Update documentation
  * Fix config file in phar
  * Insight remarks
  * Up the dependencies
  * Symfony skeleton fixes
  * Modular PHP config for nginx
  * Basic split up nginx config
  * Merge pull request #34 from kimausloos/master
  * Fix notice about uninitialized index
  * Merge pull request #29 from Kunstmaan/pingdom
  * Remove pingdom dependency
  * Fix for Apache 2.4
  * Fix: the download url for the production release

0.0.4/ 2014-08-30
==================

 * Release: Update documentation
 * Fix: Check if pingdom credentials are configured
 * Fix password
 * Fix: Add postgres settings and create the tomcat folder
 * Fix: New tomcat version
 * Fix: Permissions while symlinking
 * Fix: Creation fails because site url isn't set
 * Fix: Revert to travis.yml
 * Fix: run composer install
 * Fix: Don't configure nginx
 * Fix: Move to a separate before script with nginx installed
 * Fix: Change contactid to array instead of csv string
 * Fix: Set mysql password to run the commands
 * Improvement: Add maintenance method
 * Fix undefined variable aliasses
 * Fix: create /home/projects
 * Fix: getting Travis running again
 * Improvement: Update documentation
 * Fix: More scrutiniser fixes
 * Fix: fix a lot of scrutiniser errors
 * Fix: Scrutinizer Auto-Fixes
 * Fix: Text files should end with a newline character (fixes #24 and #25)
 * Fix: Unused method, property, variable or parameter (fixes #23)
 * Fix: Remove stale config files and fix templates should not be too long (fixes #22)
 * Fix: Logical operators should be avoided (fixes #18)
 * Fix: Fixed unused method, property, variable or parameter (fixes #20)
 * Fix: Remove unused method, property, variable or parameter (fixes #19)
 * Fix: Removed unused use statement (fixes #21)
 * Fix: Reset config.yml
 * Fix: Move create-check to create function and add remove option
 * Feature: First bits of Pingdom skeleton. Can add a pingdom check when maintenance is ran
 * Improvement: Only check on filesize for rsync
 * Fix: Correct location directive
 * Fix: Undefined variable: configcontent
 * Fix: Also remove fpm-config when from symfony skeleton when running maintenance
 * Improvement: Fpm config file is embedded in nginx config now
 * Improvement: Add --delete to rsync to remove obsolete local files
 * Fix: Only generate a default nginx config at project creation. Then allow customizations
 * Fix: Fix anacrontab generation
 * Fix: Correct nginx error and accesslog filename
 * Feature: Add maintenance quick option without fixperms
 * Fix: Add fallback to app.php for images which are not in the cache

0.0.3 / 2014-07-22
==================

 * Bugfix: Fix errors related to the removal of the awstats skeleton
 * Improvement: Enable caching for woff-filetypes
 * Bugfix: Fix php5.5 opcode cache invalidation bug
 * Feature: Remove awstats skeleton and set some better defaults for the nginx config
 * Improvement: Downsize fpm pool and set max.requests
 * Feature: Enable php-fpm logging in nginx error log
 * Feature: Add caching headers for static files
 * Bugfix: Fix the correct readme source
 * Bugfix: Installdirectory bin should be /bin
 * Bugfix: Add -L option to curl to fix redirects
 * Feature: Add nginx skeleton to config.yml
 * Feature: inital nginx support
 * Bugfix: Fix update fetch
 * Bugfix: Also change the filemode for the project itself
 * Bugfix: setfacl errors due to incorrect replacement
 * Bugfix: fix for acl errors
 * Bugfix: dropping the database for mysql now works
 * Feature: set filemode to false in the vendors
 * Bugfix: Uninstall postgresql from Travis boxes begore installing
 * Feature: Starting the tomcat skeleton
 * Feature: Finish the Postgresql skeleton
 * Feature: Finish the Postgresql skeleton
 * Bugfix: Updated cheffile to work perfectly together with Kitchenplan
 * Bugfix: Tweak the DependencyResolver
 * Bugfix: Fix running on a real machine
 * Bugfix: Override parameters, not append
 * Quality: Update code for Scrutinizer & Insight suggestions

0.0.2 / 2014-01-01
==================

 * Bugfix: Bring “permissions” command in line
 * Docs: Add the Chef installation to the docs
 * Bugfix: Output line in permissions command was not updated
 * Docs: Update the docs and add some more commands to the Travis tests
 * Feature: Creating the installer file
 * Bugfix: Only run the applied skeletons during maintenance
 * Bugfix: Handle more scenario’s for —no-interaction
 * Feature: Add a --no-interaction switch
 * Bugfix: Make sure /etc/php5/fpm/pool.d/ exists
 * Feature: Provision the Travis box with the chef-skylab coobook
 * Feature: Add the PHP and Symfony skeletons
 * Feature: add a Vagrantfile and Cheffile to run a clean Ubuntu 13.10 Skyab server
 * Feature: AWStats skeleton implemented
 * Improvement: Introduce a trait to setup the providers, and add a RemoteProvider
 * Improvement: Use a template for 000firsthost.conf
 * Bugfix: Fix RemoveCommand
 * Feature: Added the FetchCommand
 * Refactoring: Huge code cleanup by removing $output and a better progress view
 * Bugfix: Use central file write method
 * Update description
 * Feature: Load config and twig templates from within the phar file
 * Feature: The ApacheSkeleton is implemented
 * Bugfix: Source formatting was broken
 * Bugfix: Clean up inspection results
 * Feature: Add GZ to phar compilation
 * Bugfix: Consistent use of / in paths
 * Feature: ApacheSkeleton implemented
 * Bugfix: Missing fcron.d folder on new projects
 * Feature: Add documentation and a documentation generator
 * Feature: AnacrontabSkeleton ported
 * Feature: Complete the ApplySkeletonCommand and SetPermissionsCommand
 * Bugfix: Remove debugging code int he self-update command

0.0.1 / 2013-12-19
==================

 * Feature: Compilation to a phar and self-update from Github releases
 * Feature: Enable running skylab as a user, and properly handling elevating rights using sudo
 * Feature: BackupCommand and RemoveProjectCommand are fully functional
 * Feature: Nice looking progres indicators for long running tasks, and improved debug information using -v
 * Feature: R/W of permissions.xml, backup.xml and ownership.xml config files
 * Feature: Initial porting of kServer and kDeploy code
 * Feature: Initial Cilex application structure
