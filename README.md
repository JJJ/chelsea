# Chelsea 🥾

A versatile, custom WordPress bootloader using `SHORTINIT`.

----

`SHORTINIT` is a PHP constant that WordPress looks for *relatively* early inside of `wp-settings.php`. When defined as truthy, WordPress will `return false` before the majority of files inside of `wp-includes` are `require`d and after `shutdown_action_hook()` is registered as a shutdown function.

This is awesome if you only want to load *some* of a WordPress installation while skipping things like: plugins, themes, posts, taxonomies, comments, localization, blocks, sitemaps, REST API, etc...

## Usage

1. Copy `chelsea.php` into the root directory of your WordPress installation, naming it whatever you prefer.
1. Customize this fancy new file until it is doing what you want it to do
1. 💗

## History

WordPress *traditionally* uses `index.php` in the root directory as the primary point of entry for server software like NGINX to route requests to:

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

(Other files get accessed directly: `wp-cron.php`, `wp-login.php`, `xmlrpc.php`, etc... and Chelsea is conceptually similar.)

## How

Add an NGINX directive to point a location to `chelsea.php` and restart NGINX:

```nginx
location /app {
    try_files $uri $uri/ /chelsea.php?$args;
}
```

When NGINX sees someone visiting `example.org/app` it will try to serve that request using `chelsea.php` as the point of entry and pass along with it any of the arguments in the query string. Neat!

## Why

You want all the power and flexibility of WordPress, but you do not want the entire set of user-facing features that it comes bundled with.

You are familiar with WordPress, and like it, and would totally build your next big thing with it – if only it weren't so bloated or slow or old or whatever other bad things people say about it.

With Chelsea, now you can! 🥫 `@see: can`

## Includes

By default, Chelsea includes support for:

* parsing requests
* handling 404's
* users
* user roles & capabilities
* user sessions
* default constants
* the `$wp`, `$wp_query`, `$wp_the_query` globals
* the `chelsea_do_parse_request_fatal_error_fixer()` function will attempt to lazy-load *some* files if you have not manually included them, but only if you are using WordPress to parse the request and have not built your own parser

You are encouraged to fork/copy the contents of `chelsea.php`, modify them, and explore what is possible.

## Caveats

Chelsea currently includes `wp-load.php` with `SHORTINIT` set. This means you are still stuck with *some* low-level configurations & features, like:
* `wp-config.php` is included if it exists, and errors if it cannot be found by `wp-load.php`
* The `$wpdb` global via the wpdb interface class, as well as the connection settings inside `wp-config.php`
* Authentication keys and salts in `wp-config.php`
* Any custom ini settings or constants in `wp-config.php`
* error_reporting
* object & output caching
* fatal error recovery
* maintenance mode
* debug mode
* formatting functions via `formatting.php`
* a registered shutdown function that invokes the `shutdown` action and calls `wp_cache_close()` for you

If you do not want these things either, you will need to steal & strip what you want from `wp-load.php`, `wp-config.php` and `wp-settings.php` like it's a 90's Honda Civic, and bolt all the good stuff directly into `chelsea.php`.

This is totally doable, even though it feels a little weird. *(Perhaps a future version of Chelsea could do this, too?)*

## Oh yeah

Issues and pull requests encouraged. Thank you for reading this far.

Happy booting 💜
