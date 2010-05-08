=== Nonce, Please! ===
Contributors: lilyfan
Tags: anti spam, spam, comment, trackback
Requires at least: 2.3
Tested up to: 3.0
Stable tag: 1.2.0

"Nonce, Please!" is a simple plugin that prevents brute-force comments and trackbacks from spammer. This is *NOT* an alternatives to Akismet.

== Description ==

Akismet is a great plugin to block spam comments/trackbacks. It detects spams completely. But, Akismet allows to accept unsolicited feedbacks, and to store them in the database. It is weaker act to fight with spams. A better way is to reject them.

Also, the architecture of WordPress is vulnerable for spammer. Because the comment API is fixed URL like: "wp-comments-post.php", "wp-trackback.php, or "http://blog.example.com/archives/99/trackback/". Therefore, spammers can easily post bulk comments/trackbacks to WordPress weblogs.

"Nonce, Please!" add a nonce (random strings) to the comment hidden field and/or the trackback URL. A valid comment and/or trackback should have a nonce string. Bulk feedbacks will not have nonce.
This plugins also verifies that a new comment/trackbacks has the valid nonce. If there is no nonce or an invalid one, the feedback is rejected.

Adding and verifying nonce is automatic, users are not do anything!

== Screenshots ==

1. A random string (nonce) is added to the trackback URI.

== Requirements ==

* WordPress 2.3 or later
* PHP 4.2 or later

== Installation ==

1. Unzip a zip archive and put only the nonce_please.php file into your plugins directory (may be `wp-content/plugins`) of the server.
1. Activate the plugin.

== License ==

The license of this plugin is GPL v2.

== Restrictions ==

* If you are using cacheing plugins (such as WP-Cache, WP Suer Cache), make sure that caching time less than 12 hours. Because WordPress nonce string will change in 12 hours cycle and valid for 24 hours. If caching longer than 12 hours, invalid nonce will be survived at your site.

== Getting a support ==

To get support for this plugin, please send an email to ikeda.yuriko+nonceplease _@_ GMAIL COM. (You need adjust to valid address)

== Frequently Asked Questions ==

Q: Will Akismet no use when I use "Nonce, Please!"?

A: No. This plugin detect only bulk feedbacks that are sent to hard-coded comment/trackback URLs as "http://blog.example.com/wp-trackback.php?p=NNN." I suggest keep using Akismet. You will see fewer spams at Akismet admin panel!

== Translations ==

* Belarusian (be) - [Marcis G.](http://pc.de/)
* Japanese (ja) - [IKEDA Yuriko](http://en.yuriko.net/)

== Changelog ==

= 1.2.0 (2010-05-08) =
* Unified the plugin name to "Nonce, Please!" along the nonce_please.php file. "Nonce! Please" is now a wrong spelling.
* Error messages are now translated.
* Add Belarusian(be) translation.
* Fixed the URL of the Plugin URI.
* Change the name of languages resource directory from "lang" into "languages".
* Fix the language resource path to show translated description, author name properly.

= 1.1.2 (2010-01-06) =
* Fix HTML syntax error at displaying a trackback URL when using standard permalink (?p=NNNN etc)

= 1.1.1 (2009-06-08) =
* At version 1.1.1, fixed the error that the nonce is not properly checked and spams are passed through.

= 1.1 (2009-03-23) =
* Skip checking a nonce for log-in users. Therefore, you can reply a comment at the admin panel without errors.

= 1.0 (2008-07-07) =
* Initial version

== Upgrade Notice == 

= 1.1.2 =
The weblog using standard permalink (like ?p=1234) should upgrade. The syntax error at trackback URL will repaired.
