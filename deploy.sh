#!/bin/bash

rsync -vvr /var/www/html/Satan/broker/module /mnt/tmp/var/www/html/bidder/module
rsync -vvr /var/www/html/Satan/broker/public /mnt/tmp/var/www/html/bidder/public
