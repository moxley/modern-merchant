; INSTALLER CONFIGURATION
; 
; This file (config_install.ini.tpl) is a template for the CLI-based Modern Merchant installer.
;
;
; INSTRUCTIONS
;
; 1. Rename this file to "config_install.ini", and change the values to match your environment.
; 2. Run the installer script from the mm/ directory:
;      php scripts/install.php
;    Or, on *nix system:
;      scripts/install
;
; You may also save the configuration file as $HOME/.modernmerchant_config_install.ini,
; where $HOME is your account's home directory.

upgrade              = false
debug_mode           = true

database.name        = modern
database.host        = localhost
database.user        = modern
database.password    = modern
database.port        = 3306

urls.http            = http://localhost
urls.https           = http://localhost
urls.mm_root         = /

admin.username       = admin
admin.password       = admin
