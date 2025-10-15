#!/bin/sh
# Runs Dart Sass on non CDS CSS files.

sass -w assets/sass:assets/css --style expanded
