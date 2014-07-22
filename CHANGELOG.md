
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
