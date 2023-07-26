# VTV Data Importer Tool

## Requirements

- Apache / Nginx
- PHP 8.2 with xml and redis extension
- Supervisor
- Beanstalk
- Redis

## Installation
**Redis**

```bash
$ pecl install redis
```

**Beanstalk**

```bash
$ sudo apt-get install beanstalkd
```

**Supervisor**

```bash
$ sudo apt update && sudo apt install supervisor
$ sudo systemctl status supervisor
```

**Clone and Install**

```bash
$ git clone git@git.pixelpark.com:cgn-client-bmas/bmas-vtv/vtv-data-importer.git
$ cd vtv-data-importer  
$ composer install
```

**Running diagnostics**

Diagnostics can be run from the console. 
It checks whether all necessary dependencies and extensions
are installed and services are running.

```bash
$ bin/console vtv:diagnostics

Starting diagnostics:
  OK   Cpu Performance
  OK   Php Version: Current PHP version is 8.2.8
  OK   Extension Loaded: redis,xml,bcmath,intl,zip,Zend OPcache extensions are loaded.
  OK   Security Advisory: There are currently no security advisories for packages specified in composer.lock
  OK   Dir Writable: The path is a writable directory.
  OK   Disk Free: Remaining space at ./data: 21.38 GiB
  OK   Class Exists: All classes are present.
  OK   Web Server is working.
  OK   Redis is working.
  OK   Callback: Beanstalk is working

OK (10 diagnostic checks)              
```

**Additional tools**

`agaveapi/beanstalkd-console` and `redislabs/redisinsight`

## Configuration

Example of `.env` configuration

```bash 
# API configuration
API_BASE_URI="http://example.de:8080/"

# Beanstalk
BEANSTALK_HOST="beanstalk_host"
BEANSTALK_PORT=11300
BEANSTALK_TUBE="vtv"
BEANSTALK_PRIORITY=1024
BEANSTALK_DELAY=0

# Redis
REDIS_HOST="redis_host"
REDIS_PORT=6379
```

Supervisor configuration for the worker

```bash
# /etc/supervisor/conf.d/WorkerDispatchJob.conf
[program:WorkerDispatchJob]
command=bin/console vtv:worker:start
process_name=%(program_name)s_%(process_num)02d
stdout_logfile=/var/log/supervisor/%(program_name)s.log
numprocs=10
startretries=30
directory=/var/www
autostart=true
autorestart=true
user = root
```


## CLI

List of the commands

```bash 
$ bin/console

# Output
Available commands:
  completion                 Dump the shell completion script
  help                       Display help for a command
  list                       List commands
 vtv
  vtv:diagnostics            Performing application diagnostic tests
  vtv:producer:create-jobs   The producer creates and puts the jobs in the queue
  vtv:producer:delete-cache  Drop the producer cache
  vtv:worker:start           The worker sends HTTP requests to the API endpoint
```

