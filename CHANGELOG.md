
0.0.13 / 2015-06-10
==================

 * Merge pull request #67 from wimvds/fix-drupal-backups
 * Merge pull request #64 from Kunstmaan/feature/fixcron

0.0.12 / 2015-04-07
==================

 * Fix broken backup paths
 * Enable sentry in developer mode again (Reverted)
 * Added support for @project.rootpath@ and @config.hostmachine@ (from kDeploy)

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
