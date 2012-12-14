php-googleplaces
================

PHP Wrapper for the Google Places API.

Origins
-------

Simply put, when I Googled "php google places" I was presented with [Google-Places---PHP-](https://github.com/anthony-mills/Google-Places---PHP-). I attempted to use it, and it was fine as I was able to make it work, but there seemed to be a huge assumption that you already knew a lot of the quirks of the Google Places API.

I did not fit into this assumption and set out to built my own wrapper with a heavy focus on sanity checking inputs, utilizing the magical parts of PHP (reads: you interact directly with the object as you would the API) and avoiding setter methods as they were put here by the devil.

I didn't fork because I was going to change too much and I highly doubt my pull requests would have been accepted.

Usage Examples
--------------

### Getting started

```php
require_once 'GooglePlaces.php';
$google_places = new GooglePlaces('_YOUR_API_KEY');
```

### Search nearby, ranked by prominence

```php
$google_places->location = array(-33.86820, 151.1945860);
$google_places->radius   = 800;
$results                 = $google_places->searchNearby();
```

### Search nearby, ranked by distance

```php
$google_places->location = array(-33.86820, 151.1945860);
$google_places->rankby   = 'distance';
$google_places->types    = 'restaurant'; // Requires keyword, name or types
$results                 = $google_places->searchNearby();
```

The Fututre
-----------

Assuming December 21st, 2012 doesn't lead to humanity's demise, the plan is to continue implementing the entire Google Places API into this class and eventually migrating it into my PHP Framework [PICKLES](https://github.com/joshtronic/pickles).
