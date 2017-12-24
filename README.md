# FenTT
FenTT is a MediaWiki extension that renders high quality chess diagrams described in FEN using only TrueType fonts and CSS style.

## Usage

Usage instructions are contained in the package. To view it,
* Download the package.
* Run the command
```
  make doc
```
* Open file `doc/reference.html` in a browser.

## Installation

The simplest is to clone the git repository in folder `extensions` of your wiki and run the `install`
target:

```bash
cd /path/to/your/wiki/extensions
git clone --depth 1 https://github.com/xeyownt/mwfentt FenTT
cd FenTT
make install
```

`make install` will delete all files that are not necessary for the extension to run, and add the
following line at the end of `LocalSettings.php` (MW 1.25 or above):
```php
wfLoadExtension( 'FenTT' );
```

Done! Navigate to `Special:Version` on your wiki to verify that the extension is successfully installed.

## Links

* [FenTT extension page on mediawiki.org](http://www.mediawiki.org/wiki/Extension:FenTT).
* [Homepage](http://mip.noekeon.org/mwfentt/reference.html).
* [Source code on GitHub](https://github.com/xeyownt/mwfentt).

## License

Copyright (C) 2007-2016  Michael Peeters `<https://github.com/xeyownt>`.

The FenTT MediaWiki extension comes with ABSOLUTELY NO WARRANTY. This is free software; you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

See file COPYING for more details.
