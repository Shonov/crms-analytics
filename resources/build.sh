#!/usr/bin/env bash

echo "Run build"
yarn run build

echo "Move index.html to views"
mv ./dist/index.html ./views/welcome.blade.php

echo "Remove public static folder"
rm -rf ././../public/static

echo "Move static folder"
mv ./dist/static/ ././../public/static/

echo "Done"

