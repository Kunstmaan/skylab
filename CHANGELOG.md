
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
