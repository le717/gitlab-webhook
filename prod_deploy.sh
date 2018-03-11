#!/bin/bash

cd /var/www/site/
git checkout master
git pull origin master >> /home/deploy/deploy.log
echo "" >> /home/deploy/deploy.log
