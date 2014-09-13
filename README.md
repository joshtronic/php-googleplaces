# php-googleplaces

[![Build Status](http://img.shields.io/travis/joshtronic/php-googleplaces.svg?style=flat)][travis]
[![Coverage Status](http://img.shields.io/coveralls/joshtronic/php-googleplaces.svg?style=flat)][coveralls]
[![Downloads](http://img.shields.io/packagist/dm/joshtronic/php-googleplaces.svg?style=flat)][packagist]
[![Gittip](http://img.shields.io/gittip/joshtronic.svg?style=flat)][gittip]

[travis]:    http://travis-ci.org/joshtronic/php-googleplaces
[coveralls]: https://coveralls.io/r/joshtronic/php-googleplaces
[packagist]: https://packagist.org/packages/joshtronic/php-googleplaces
[gittip]:    https://www.gittip.com/joshtronic/

PHP Wrapper for the Google Places API.

## Origins

Simply put, when I Googled “php google places” I was presented with
[Google-Places---PHP-](https://github.com/anthony-mills/Google-Places---PHP-).
I attempted to use it, and it was fine as I was able to make it work, but
there seemed to be a huge assumption that you already knew a lot of the quirks
of the Google Places API.

I did not fit into this assumption and set out to built my own wrapper with a
heavy focus on sanity checking inputs, utilizing the magical parts of PHP
(reads: you interact directly with the object as you would the API) and
avoiding setter methods as they were put here by the devil.

I opted not to fork because I was going to change too much and I highly doubt
my pull requests would have even been accepted.

## Installation

The preferred installation is via `composer`. First add the following to your
`composer.json`

```json
"require": {
    "joshtronic/php-googleplaces": "dev-master"
}
```

Then run `composer update`

## Usage

### Getting started

```php
$google_places = new joshtronic\GooglePlaces('_YOUR_API_KEY_');
```

### Search nearby, ranked by prominence

```php
$google_places->location = array(-33.86820, 151.1945860);
$google_places->radius   = 800;
$results                 = $google_places->nearbySearch();
```

### Search nearby, ranked by distance

```php
$google_places->location = array(-33.86820, 151.1945860);
$google_places->rankby   = 'distance';
$google_places->types    = 'restaurant'; // Requires keyword, name or types
$results                 = $google_places->nearbySearch();
```

### Second page of search nearby results

```php
$google_places->pagetoken = $results['next_page_token'];
$page2_results            = $google_places->nearbySearch();
```

### Radar search

```php
$google_places->location = array(-33.86820, 151.1945860);
$google_places->radius   = 800;
$google_places->types    = 'restaurant'; // Requires keyword, name or types
$results                 = $google_places->radarSearch();
```

### Place details

```php
$google_places->reference = '#reference#'; // Reference from search results
$details                  = $google_places->details();
```

## The Future

The project that I created this library for is now defunct so I’m not actively
using it or hacking on it. That being said, if there’s something you want to
see included or you find a bug, just open an issue or grow a pair and fork,
hack and send a pull request ;)
