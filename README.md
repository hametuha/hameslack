# HameSlack

Tags: slack  
Contributors: Takahashi_Fumiki, hametuha  
Tested up to: 6.4  
Requires at least: 5.9  
Requires PHP: 7.2  
Stable Tag: nightly  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

A Slack utility for WordPress.

## Description

This plugin integrates [Slack](https://slack.com) and WordPress.

### Core Conception

By default, this plugin does nothing. It's true. 
Slack has many API intergrations, but **hameslack** uses 2 of them.

- [Incoming Webhook](https://api.slack.com/incoming-webhooks) to post to slack.
- [Custom Bot](https://api.slack.com/bot-users) to interact with slack.

Upper is easier. This plugin helps the connection between Slack and WordPress and you can concentrate on what you should do with slack.

### Use Cases

Here is a list of use case of us on WordPress with many editors.

- **Easy** Post notification to slack if some post is awaiting review.
- **Bit Difficult** Post accesss summary to slack once a week, because my collegue doesn't open Google Analytics.
- **Very Difficult** Convert slack conversation to single post and make interview post.

For more details, please read our [Documentation](https://gianism.info/add-on/hameslack/). We have some samples.

### How to Integrate

The simplest usage is *post to slack*. You can do like below:

<pre>
do_action( 'hameslack', $text_to_post, $attachments, $channel );
</pre>

Function is also available, but I prefer to use `do_action` to avoid annoying `if ( function_exists('func_name')) `.

Everything works fine if you set properly.

### Deprecated API

- **Sending Invitation** <code>deprecated at 2.0</code>:  this endpoint was unofficial and official one is only for Enterprise grid. Users now can send invitation reqeust from their profile page.
- **Outgoind Webhook** <code>deprecated at 2.0</code>: Slack recommends Events API instead.

## Install

### From Plugin Repository

Click install and activate it.

### From Github

Download and you can use it. Any pull requests are welcomed.

### Enter API Key

At least, you need [Slack Payload URL for Incoming Webhooks](https://api.slack.com/incoming-webhooks). For more details, go to our [support site](https://gianism.info/add-on/hameslack/).

### Do something

As mentioned avobe, this plugin does nothing by default. Please read our [Documentation](https://gianism.info/add-on/hameslack/).

If you have any request, please make issue on [github](https://github.com/hametuha/hameslack).

## Screenshots

1. You can create such kind of bot.
2. You can set up everything on setting screen.
3. You can create Outgoing Webhooks as custom post type.

## Changelog

### 2.0.0

- Remove invitation feature and users now can simple send message to specific channel to request invitation.
- Bot is required.

### 1.2.0

- If [gianism](https://wordpress.org/plugins/gianism) is enabled, you can log in with Slack account.

### 1.1.1

- Bugfix on REST API.

### 1.1.0

- Add invitation request feature.

### 1.0.2

- Add auto deploy.

### 1.0.0

- First release.
