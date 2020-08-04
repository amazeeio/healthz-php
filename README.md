# Healthz PHP

This is a simple PHP app used to determine the overall health of a site hosted on Lagoon.

As it stands, it relies on Lagoon conventions to determine which services are available
and then checks if the services are up.

## Configuration

This script should reside in a directory separate from the application that is being hosted.
For instance, the convention is that a webapp is stored in `/app` - you could store Healthz-php
in `/healthz`. 

```
  location /lagoonhealthz {
    root /lagoonhealthz;
    rewrite ^/lagoonhealthz$ /lagoonhealthz/index.php;

    location ~* \.php(/|$) {
      include        /etc/nginx/fastcgi.conf;
      fastcgi_param  SCRIPT_NAME        /index.php;
      fastcgi_param  SCRIPT_FILENAME    /lagoonhealthz/index.php;
      fastcgi_pass   ${NGINX_FASTCGI_PASS:-php}:9000;
    }
  }

```

Adding this to your nginx configuration will reroute all incoming traffic on `/healthz`
to our current script.

### Setting failure response code

By default, we return an http 500 response code for any failed checks. This can be a potential problem for setups that involve negative caching of errors.

If you would like to have the system return something other than a 500, you can set the environment variable `HEALTHZ_PHP_HTTP_FAIL_CODE` with the return code.


## Extending checks

Checks can be created by implementing `AmazeeIO\Health\Check\CheckInterface` 
which principally does two things.

First, it checks whether the check is actually applicable in the present environment.
For example, to know whether it should be checking the status of the Solr service, the `CheckSolr` class
will first check whether the environment variable `SOLR_PORT` is available.
If the CheckInterface::appliesInEnvironment call to any particular check returns `true`
that check will be registered to run.

Second, all applicable checks are required to return both a `result` and a `status`.
The `status` returns one of `STATUS_PASS` `STATUS_FAIL` and `STATUS_WARN` - These three statuses
really determine the health of the service under test. They're used to determine health of the overall system.

The status of the check as a whole is determined by the _most negative_ applicable check's result.
That is, if any one of the checks return a `STATUS_FAIL`, the entire check is considered to have failed.
If any one check returns a `STATUS_WARN`, the entire system check is considered to be in a warning state.

When you have created your checks you register them in the `checks.conf.php` file. 
