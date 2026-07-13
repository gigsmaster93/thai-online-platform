# Thai Online Platform

Own WordPress-based CMS platform for migrating and running Thai Online websites.

## Goal

One platform, two separate sites:
- RU frontend / EN admin
- EN frontend / EN admin

Each site has its own database and domain, but uses the same platform architecture.

## Source data

uCoz backups are not stored in this repository.
Current RU backup path:

/home/thaionline/backups/full-backup

## Build principle

public_html is installation/runtime only.
Development happens in /home/thaionline/thai-platform.
