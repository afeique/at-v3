<?php

/*******************************************************
NOTE: This is a cache file generated by IP.Board on Tue, 11 Jun 2013 11:10:01 +0000 by Guest
Do not translate this file as you will lose your translations next time you edit via the ACP
Please translate via the ACP
*******************************************************/



$lang = array( 
'account_created' => "<#NAME#>,

Your account has been created successfully at <#BOARD_NAME#>.

If we were waiting on a parental consent form, this means the form has been received and documented.

Your details are as follows:

Username: <#NAME#>
Email Address: <#EMAIL#>
Password: <#PASSWORD#>

Please be aware that we do not store a plain text copy of your password, and you can change your password at any time through your control panel on the site.

Visit this link to join into our discussions!

<#BOARD_ADDRESS#>
",
'admin_newuser' => "
Hello,

You have received this email because a new user has completed their registration!

------------------------------------------------
Log in name: <#LOG_IN_NAME#>
Display name: <#DISPLAY_NAME#>
Email: <#EMAIL#>
IP Address: <#IP#>
Date: <#DATE#>
------------------------------------------------

You can turn off user notification in the Admin Control Panel

Have a super day!
",
'complete_reg' => "<#NAME#>,

An administrator has accepted your registration request or email address change at <#BOARD_NAME#>. You may now log in with
your chosen details and access your full user account at <#BOARD_ADDRESS#>
",
'digest_forum_daily' => "<#NAME#>,

This is your daily new topics digest for forum <#FORUM_NAME#>!

----------------------------------------------------------------------

<#CONTENT#>

----------------------------------------------------------------------

The forum can be found here:
<#URL#>

Unsubscribing:
--------------

You can unsubscribe at any time here: <#UNSUBCRIBE_URL#>
",
'digest_forum_weekly' => "<#NAME#>,

This is the digest of posts created this week in forum <#FORUM_NAME#>.

----------------------------------------------------------------------

<#CONTENT#>

----------------------------------------------------------------------

The forum can be found here:
<#URL#>

Unsubscribing:
--------------

You can unsubscribe at any time here: <#UNSUBCRIBE_URL#>
",
'digest_topic_daily' => "<#NAME#>,

This is the digest of posts in topic \"<#TITLE#>\" for today.

----------------------------------------------------------------------

<#CONTENT#>

----------------------------------------------------------------------

The topic can be found here:
<#URL#>

Unsubscribing:
--------------

You can unsubscribe at any time here: <#UNSUBCRIBE_URL#>
",
'digest_topic_weekly' => "<#NAME#>,

This is the digest of posts in topic \"<#TITLE#>\" for this week.

----------------------------------------------------------------------

<#CONTENT#>

----------------------------------------------------------------------

The topic can be found here:
<#URL#>

Unsubscribing:
--------------

You can unsubscribe at any time here: <#UNSUBCRIBE_URL#>
",
'email_convo' => "<#NAME#>,

Attached to this message is a HTML file containing a personal conversation archive:
Conversation title: <#TITLE#>
Conversation started: <#DATE#>
<#LINK#>
",
'email_member' => "<#MEMBER_NAME#>,

<#FROM_NAME#> has sent you this email from <#BOARD_ADDRESS#>


<#MESSAGE#>

---------------------------------------------------
Please note that <#BOARD_NAME#> has no control over the
contents of this message.
---------------------------------------------------

",
'error_log_notification' => "Dear admin,

An error has been generated on your forums.  You are being sent this notification based on your error log notification settings in the Admin Control Panel.  This error meets the criteria for errors that you have set to be notified about.

The error code is: <#CODE#>
The error message is: <#MESSAGE#>
The user who saw this error is: <#VIEWER#>
The IP address of this user is: <#IP_ADDRESS#>

Please login to your Admin Control Panel to use the error log viewer tool for further information.

<#BOARD_ADDRESS#>
",
'forward_page' => "
<#TO_NAME#>


<#THE_MESSAGE#>

---------------------------------------------------
Please note that <#BOARD_NAME#> has no control over the
contents of this message.
---------------------------------------------------
",
'lost_pass' => "
<#NAME#>,
This email has been sent from <#BOARD_ADDRESS#>

You have received this email because a password recovery for the
user account \"<#USERNAME#>\" was instigated by you on <#BOARD_NAME#>.

------------------------------------------------
IMPORTANT!
------------------------------------------------

If you did not request this password change, please IGNORE and DELETE this
email immediately. Only continue if you wish your password to be reset!

------------------------------------------------
Activation Instructions Below
------------------------------------------------

We require that you \"validate\" your password recovery to ensure that
you instigated this action. This protects against
unwanted spam and malicious abuse.

Simply click on the link below and complete the rest of the form

<#THE_LINK#>

(Some email client users may need to copy and paste the link into your web
browser).

------------------------------------------------
Not working?
------------------------------------------------

If you could not validate your password recovery request by clicking on the link, please
visit this page:

<#MAN_LINK#>

It will ask you for a user id number, and your validation key. These are shown
below:

User ID: <#ID#>

Validation Key: <#CODE#>

Please copy and paste, or type those numbers into the corresponding fields in the form.

------------------------------------------------
Is this not working?
------------------------------------------------

If you cannot re-activate your account, it's possible that the account has been removed or you
are in the process of another activation, such as registering or changing your registered email address.
If this is the case, then please complete the previous activation.
If the error persists, please contact an administrator to rectify the problem.

IP address of sender: <#IP_ADDRESS#>

",
'lost_pass_email_pass' => "
<#NAME#>,
This email has been sent from <#BOARD_ADDRESS#>

This email completes your lost password request.

------------------------------------------------
YOUR NEW PASSWORD
------------------------------------------------

Your username is: <#USERNAME#>
Your email address is: <#EMAIL#>
Your new password is: <#PASSWORD#>

Log in here: <#LOGIN#>

Please be careful to use the correct information (username or email address) to login to the site.

------------------------------------------------
CHANGING YOUR PASSWORD
------------------------------------------------

Once you've logged in, you can visit your User CP to
change your password.

User CP: <#THE_LINK#>

",
'new_comment_added' => "<#MEMBERS_DISPLAY_NAME#>,

<#COMMENT_NAME#> has left you a comment on your profile.

Manage your comments: <#LINK#>
",
'new_comment_request' => "<#MEMBERS_DISPLAY_NAME#>,

<#COMMENT_NAME#> has left you a comment that requires your approval.

As you've chosen to approve all new comments the new comment will not appear on your
profile until it's been approved.

Log in and then manage your comments: <#LINK#>
",
'new_friend_added' => "<#MEMBERS_DISPLAY_NAME#>,

<#FRIEND_NAME#> has successfully added you to their friends list.

Manage your friends: <#LINK#>
",
'new_friend_approved' => "<#MEMBERS_DISPLAY_NAME#>,

<#FRIEND_NAME#> has approved your friend request!

Log in and then manage your friends: <#LINK#>
",
'new_friend_request' => "<#MEMBERS_DISPLAY_NAME#>,

<#FRIEND_NAME#> wants to be your friend!

This message has been sent because <#FRIEND_NAME#> has added you to their friends
list. As you've chosen to approve all friend requests, you will need to visit your
friends list and approve them.

Log in and then manage your friends: <#LINK#>
",
'new_likes' => "Hello!

<#MEMBER_NAME#> just liked a post you made!
======================================================================
<#SHORT_POST#>
======================================================================
<#URL#>
",
'new_post_queue_notify' => "Hello!

This email has been sent from: <#BOARD_NAME#>.

A new post has been made and is awaiting approval.

----------------------------------
Topic: <#TOPIC#>
Forum: <#FORUM#>
Author: <#POSTER#>
Time: <#DATE#>
Manage: <#LINK#>
----------------------------------
<#POST#>
----------------------------------

If you no longer require notification, you can stop these emails by simply
removing your email address from the forum settings options.

<#BOARD_ADDRESS#>

",
'new_status' => "<#NAME#>,

<#OWNER#> has just posted a new status update.

======================================================================
<#STATUS#>
======================================================================


You can turn off status notification by visiting <#URL#>
",
'new_topic_queue_notify' => "Hello!

This email has been sent from: <#BOARD_NAME#>.

A new topic has been posted and is awaiting approval.

----------------------------------
Topic: <#TOPIC#>
Forum: <#FORUM#>
Author: <#POSTER#>
Time: <#DATE#>
Manage: <#LINK#>
----------------------------------
<#POST#>
----------------------------------

If you no longer require notification, you can stop these emails by simply
removing your email address from the forum settings options.

<#BOARD_ADDRESS#>

",
'newemail' => "
<#NAME#>,
This email has been sent from <#BOARD_ADDRESS#>

You have received this email because you requested an
email address change.

------------------------------------------------
Activation Instructions Below
------------------------------------------------

We require that you \"validate\" your email address change to ensure that
you instigated this action. This protects against
unwanted spam and malicious abuse.

To activate your account, simply click on the following link:

<#THE_LINK#>

(Some email client users may need to copy and paste the link into your web
browser).

------------------------------------------------
Not working?
------------------------------------------------

If you could not validate your email address change by clicking on the link, please
visit this page:

<#MAN_LINK#>

It will ask you for a user id number, and your validation key. These are shown
below:

User ID: <#ID#>

Validation Key: <#CODE#>

Please copy and paste, or type those numbers into the corresponding fields in the form.

Once the activation is complete, you may need to log back in to update your member group
permissions.

------------------------------------------------
Help! I get an error!
------------------------------------------------

If you cannot re-activate your account, it's possible that the account has been removed or you
are in the process of another activation, such as registering or changing your registered email address.
If this is the case, then please complete the previous activation.
If the error persists, please contact an administrator to rectify the problem.

",
'personal_convo_invite' => "<#NAME#>,

<#POSTER#> has added you to their personal conversation entitled \"<#TITLE#>\".

You can read this personal conversation by following the link below:

<#BOARD_ADDRESS#><#LINK#>
",
'personal_convo_new_convo' => "<#NAME#>,

<#POSTER#> has sent you a new personal conversation entitled \"<#TITLE#>\".

<#POSTER#> said:
======================================================================
<#TEXT#>
======================================================================

PLEASE DO NOT REPLY DIRECTLY TO THIS EMAIL!
You can reply to this personal conversation by following the link below:

<#BOARD_ADDRESS#><#LINK#>
",
'personal_convo_new_reply' => "<#NAME#>,

<#POSTER#> has replied to a personal conversation entitled \"<#TITLE#>\".

<#POSTER#> said:
======================================================================
<#TEXT#>
======================================================================

PLEASE DO NOT REPLY DIRECTLY TO THIS EMAIL!
You can reply to this personal conversation by following the link below:

<#BOARD_ADDRESS#><#LINK#>
",
'possibleSpammer' => "

Hello,

You have received this email because you chose to be notified when a possible spammer is flagged.

Name: <#MEMBER_NAME#>
Email: <#EMAIL#>
IP: <#IP#>
Registered: <#DATE#>

You can view this user here: <#LINK#>

Have a super day!
",
'post_was_quoted' => "Hello!

This message is to notify you that one of your posts has been quoted by <#MEMBER_NAME#>.

The post that was quoted can be found here:

<#ORIGINAL_POST#>

The post that <#MEMBER_NAME#> submitted can be found here:

<#NEW_POST#>

----------------------------------
<#POST#>
----------------------------------

If you no longer wish to receive notifications of quoted posts, you can adjust your preferences on the
community by clicking My Settings, and then choosing Notification Options.

<#BOARD_ADDRESS#>

",
'reg_validate' => "
<#NAME#>,
This email has been sent from <#BOARD_ADDRESS#>

You have received this email because this email address
was used during registration for our forums.
If you did not register at our forums, please disregard this
email. You do not need to unsubscribe or take any further action.

------------------------------------------------
Activation Instructions
------------------------------------------------

Thank you for registering.
We require that you \"validate\" your registration to ensure that
the email address you entered was correct. This protects against
unwanted spam and malicious abuse.

To activate your account, simply click on the following link:

<#THE_LINK#>

(Some email client users may need to copy and paste the link into your web
browser).

------------------------------------------------
Not working?
------------------------------------------------

If you could not validate your registration by clicking on the link, please
visit this page:

<#MAN_LINK#>

It will ask you for a user id number, and your validation key. These are shown
below:

User ID: <#ID#>

Validation Key: <#CODE#>

Please copy and paste, or type those numbers into the corresponding fields in the form.

If you still cannot validate your account, it's possible that the account has been removed.
If this is the case, please contact an administrator to rectify the problem.

Thank you for registering and enjoy your stay!
",
'send_text' => "I thought you might be interested in reading this web page: <#THE LINK#>

From,

<#USER NAME#>
",
'status_reply' => "<#NAME#>,

<#POSTER#> <#BLURB#>

Status: (<#OWNER#>) <#STATUS#>
======================================================================
<#TEXT#>
======================================================================


You can turn off status notification by visiting <#URL#>
",
'subject__account_created' => "Your account has been created",
'subject__complete_reg' => "Account: %s, validated at %s",
'subject__digest_forum_daily' => "Your daily new topics digest",
'subject__digest_forum_weekly' => "Your weekly new topics digest",
'subject__digest_topic_daily' => "Your daily new posts digest",
'subject__digest_topic_weekly' => "Your weekly new posts digest",
'subject__email_convo' => "Personal Conversation Archive",
'subject__error_log_notification' => "An error has been generated on your site",
'subject__new_comment_added' => "<a href='%s'>New comment</a> from <a href='%s'>%s</a>",
'subject__new_comment_request' => "<a href='%s'>New comment</a> from <a href='%s'>%s</a> pending approval",
'subject__new_friend_added' => "<a href='%s'>%s</a> added you as a friend",
'subject__new_friend_approved' => "<a href='%s'>%s</a> approved your friend request",
'subject__new_friend_request' => "<a href='%s'>%s</a> sent you a <a href='%s'>friend request</a>",
'subject__new_likes' => "<a href='%s'>%s</a> liked a <a href='%s'>post you made</a> in <a href='%s'>%s</a>",
'subject__new_post_queue_notify' => "New Post Awaiting Approval",
'subject__new_status' => "<a href='%s'>%s</a> has posted a new <a href='%s'>status update</a>",
'subject__new_topic_queue_notify' => "New Topic Awaiting Approval",
'subject__other_status_reply' => "<a href='%s'>%s</a> has replied to <a href='%s'>%s</a>'s <a href='%s'>status</a>",
'subject__personal_convo_invite' => "<a href='%s'>%s</a> has added you to a <a href='%s'>personal conversation</a>",
'subject__personal_convo_new_convo' => "<a href='%s'>%s</a> started a new <a href='%s'>personal conversation</a> with you",
'subject__personal_convo_new_reply' => "<a href='%s'>%s</a> has replied to a <a href='%s'>personal conversation</a>",
'subject__post_was_quoted' => "<a href='%s'>%s</a> <a href='%s'>quoted</a> a <a href='%s'>post you made</a>",
'subject__status_reply' => "<a href='%s'>%s</a> has replied to your <a href='%s'>status</a>",
'subject__subs_new_topic' => "<a href='<#POSTERURL#>'><#POSTER#></a> posted topic <a href='<#URL#>'><#TITLE#></a>",
'subject__subs_with_post' => "<a href='<#POSTERURL#>'><#POSTER#></a> replied to <a href='<#URL#>'><#TITLE#></a>",
'subject__subs_with_post.emailOnly' => "New reply to %s",
'subject__their_status_reply' => "<a href='%s'>%s</a> has made a reply to their <a href='%s'>status</a>",
'subs_new_topic' => "<#NAME#>,

<#POSTER#> has just posted a new topic entitled \"<#TITLE#>\" in forum \"<#FORUM#>\".
<div class='callout'>
----------------------------------------------------------------------
<#POST#>
----------------------------------------------------------------------
</div>
The topic can be found here:
<a href='<#URL#>'><#URL#></a>

<div class='unsub'>
You can unsubscribe at any time here: <a href='<#UNSUBCRIBE_URL#>'><#UNSUBCRIBE_URL#></a>

If you are not following any forums and wish to stop receiving notifications, uncheck the setting
\"Send me news and information\" found in 'My Settings' under 'Notification Options'.</div>
",
'subs_with_post' => "<#NAME#>,

<#POSTER#> has just posted a reply to a topic that you have subscribed to titled \"<#TITLE#>\".
<div class='callout'>
----------------------------------------------------------------------
<#POST#>
----------------------------------------------------------------------
</div>
The topic can be found here: <a href='<#URL#>'><#URL#></a>

If you have configured in your control panel to receive immediate topic reply notifications, you may receive an
email for each reply made to this topic.  Otherwise, only 1 email is sent per board visit for each subscribed topic. 
This is to limit the amount of mail that is sent to your inbox.

<div class='unsub'>You can unsubscribe at any time here: <a href='<#UNSUBCRIBE_URL#>'><#UNSUBCRIBE_URL#></a></div>
",
 ); 
