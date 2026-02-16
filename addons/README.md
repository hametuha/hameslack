# HameSlack Addons

Addons are self-contained feature modules that extend HameSlack. Each addon is a single PHP file in this directory.

## How Addons Work

1. Files in `addons/` are auto-loaded after `functions/` and `hooks/`
2. Each addon registers itself via the `hameslack_addons` filter (always runs)
3. If the addon is not active, it returns early — no hooks are registered
4. Active addons can register settings, hooks, REST endpoints, etc.

## Creating an Addon

Create a new PHP file in this directory (e.g. `addons/my-feature.php`):

```php
<?php
defined( 'ABSPATH' ) or die();

// 1. Register addon (always runs).
add_filter( 'hameslack_addons', function ( $addons ) {
    $addons[] = [
        'id'          => 'my-feature',       // Unique ID (must match filename convention)
        'label'       => 'My Feature',       // Display name
        'description' => 'Does something.',  // Short description
    ];
    return $addons;
} );

// 2. Stop if not active.
if ( ! hameslack_is_addon_active( 'my-feature' ) ) {
    return;
}

// 3. Your feature code below.
add_action( 'init', function () {
    // ...
} );
```

## Available Helper Functions

| Function | Description |
|---|---|
| `hameslack_get_addons()` | Returns all registered addons |
| `hameslack_is_addon_active( $id )` | Check if a specific addon is active |
| `hameslack_get_active_addons()` | Returns active addons as associative array |

## Sending Messages to Slack

HameSlack provides a simple action hook to send messages to Slack via Incoming Webhook (Payload URL):

```php
do_action( 'hameslack', $content, $attachment, $channel );
```

| Parameter | Type | Description |
|---|---|---|
| `$content` | `string` | Message text |
| `$attachment` | `array` | Slack attachment array (optional, default `[]`) |
| `$channel` | `string` | Channel name (optional, defaults to `hameslack_default_channel` filter value) |

This uses the `do_action` pattern so that **your code won't break even if HameSlack is deactivated** — WordPress simply ignores unknown action hooks.

Example: notify Slack when a post is published:

```php
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
    if ( 'publish' === $new_status && 'publish' !== $old_status && 'post' === $post->post_type ) {
        do_action( 'hameslack', 'New post published: ' . get_the_title( $post ) );
    }
}, 10, 3 );
```

## Built-in Addons

### Pending Review Notify (`pending-review-notify`)

Sends a Slack notification when a post is submitted for review (transitions to `pending` status). Uses the Payload URL (Incoming Webhook).

**Settings**: Notification Channel (optional, defaults to the default channel)

### Slash Command Dashboard (`slash-command-dashboard`)

Receives Slack slash commands via a REST endpoint and displays messages in a WordPress dashboard widget. Uses the Bot Token for DM confirmations.

**Settings**: Signing Secret (required for request verification)

**Setup**:
1. Enable the addon and save settings
2. Copy the Request URL shown in settings
3. In your Slack App, create a slash command pointing to that URL
4. Copy the Signing Secret from your Slack App's Basic Information page
5. Paste the Signing Secret in the addon settings
