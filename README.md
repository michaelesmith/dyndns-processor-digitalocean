[![Build Status](https://travis-ci.org/michaelesmith/dyndns-processor-digitalocean.svg?branch=master)](https://travis-ci.org/michaelesmith/dyndns-processor-digitalocean)

# What is this?
This is a processor for the dyndns-kit framework to use Digital Ocean's API via [DigitalOceanV2](https://github.com/toin0u/DigitalOceanV2) so that . If you don't know what DynDNS-Kit is take a [look](https://github.com/michaelesmith/dyndns-kit).

# Install
`composer require "michaelesmith/dyndns-processor-digitalocean"`

# How do I use it
To see a full example usage please refer to the [example project](https://github.com/michaelesmith/dyndns-example). 

## Basic usage
```php
$doProcessor = new DigitalOceanApiProcessor(
    ['example.com'], 
    new \DigitalOceanV2\DigitalOceanV2(
        new \DigitalOceanV2\Adapter\GuzzleHttpAdapter('my_api_token)
    )
);
```

This example uses the [DigitalOceanV2](https://github.com/toin0u/DigitalOceanV2) to interact with the [Digital Ocean API](https://developers.digitalocean.com/documentation/v2/#domain-records) for updating their DNS entries.

# Contributing
Have an idea to make something better? Submit a pull request. PR's make the open source world turn. :earth_americas: :earth_asia: :earth_africa: :octocat: Happy Coding!
