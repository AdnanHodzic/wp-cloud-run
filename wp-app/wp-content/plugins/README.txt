WP Last Visit README
version 2.2, 2004-07-05

This hack will show an image next to post and comments that are new since someone's last visit to your site.

Step 1.

Put the wp-last-visit.php file in your wp-content/plugins/ directory. 


Step 2.

Enable the plugin in your options.

Click on 'Plugins' in the admin area, then 'Activate' next to the WP Grins row.


Step 3.

Add this line within the <head> tags of your index.php and wp-comments-popup.php pages.

<?php require(bloginfo('url;').'/wp-content/plugins/wp-last-visit.php?type=js'); ?>

Step 4.

Add some code to show the number of new posts and comments since your last visit

<script type="text/javascript">slvBanner();</script>

Step 5.

Add some code to show the new indicator next to each post (index.php)

<script type="text/javascript">slvShowNewIndicator(<?php print(mysql2timestamp($post->post_date)); ?>);</script>

Step 6.

Add some code to show the new indicator next to each comment (wp-comments.php and wp-comments-popup.php)

<script type="text/javascript">slvShowNewIndicator(<?php print(mysql2timestamp($comment->comment_date)); ?>);</script>

Step 7.

To show if there are new comments on a post from the link to the comments for that post, replace the comments_popup_link() in the posts loop with ak_comments_popup_link()

Step 8.

Put the 'ak-img' directory in your blog root, you can set the indicator you want (or use a text indicator) by modifying this code in wp-last-visit.php

//	var slvIndicator = 'New';
	var slvIndicator = '<img src="<?php echo $siteurl ?>/ak-img/new.gif" alt="New" title="New since your last visit." />';

remove the "//" from the first line and put it on the second line to use the text indicator.



That should be it! Let me know how it goes...

--Alex

http://www.alexking.org/