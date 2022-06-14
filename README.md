# Chelsea 🥾

A versatile, custom WordPress bootloader using `SHORTINIT`.

## Usage

1. Copy the contents of `chelsea.php` into the root directory of your WordPress installation, into a file named whatever you want it to be.
1. Customize this fancy new file of yours until you've figured out how to get it to do what you want it to do
1. 💗

## What

Use this if you only want to load *some* of a WordPress installation while skipping things like: plugins, themes, posts, taxonomies, comments, etc...

## History

WordPress traditionally uses the `index.php` in its root directory as the primary point of entry for server software like NGINX to route requests to: 

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

A few other files do get accessed directly: `wp-cron.php`, `wp-login.php`, `xmlrpc.php`, etc... and Chelsea is conceptually similar.

## How

Add an NGINX directive to point a location to `chelsea.php` and restart NGINX:

```nginx
location /app {
    try_files $uri $uri/ /chelsea.php?$args;
}
```

Now, when NGINX sees someone try to visit `example.org/app` it will try to serve that request using `chelsea.php` as the point of entry, and pass along with it any of the arguments in the query string. Neat!

## Why

You want all the power and flexibility of WordPress, but you do not want the entire set of user-facing features that it comes bundled with.

## Includes

By default, Chelsea includes support for:

* parsing requests
* handling 404's
* WPDB
* users
* user roles & capabilities
* user sessions
* default constants
* the `$wp`, `$wp_query`, `$wp_the_query` globals
* the `chelsea_do_parse_request_fatal_error_fixer()` function will attempt to lazy-load *some* files if you have not manually included them, but only if you are using WordPress to parse the request and have not built your own parser

You are encouraged to fork/copy the contents of `chelsea.php`, modify them, and explore what is possible.

Happy booting!
