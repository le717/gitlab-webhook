#!/bin/bash

cd /var/www/site/
git checkout devel
git pull origin devel >> /home/deploy/deploy.log
echo "" >> /home/deploy/deploy.log
