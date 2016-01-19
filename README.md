# mwfentt
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

* Copy the files in a directory called `FenTT/` in your wiki `extensions/` folder. At least the following files must be copied:
  * chess_merida_unicode.ttf,
  * FenTT.css, and
  * FenTT.php.
* For MediaWiki 1.25+, add the following code at the bottom of your `LocalSettings.php`:
```
wfLoadExtension( 'FenTT' );
```
* For MediaWiki 1.24 or earlier, add the following code instead:
```
require_once "$IP/extensions/FenTT/FenTT.php";
```
* Done - Navigate to `Special:Version` on your wiki to verify that the extension is successfully installed.

## Links

* [FenTT extension page on mediawiki.org](http://www.mediawiki.org/wiki/Extension:FenTT).
* [Homepage](http://mip.noekeon.org/mwfentt).
* [Source code on GitHub](https://github.com/xeyownt/mwfentt).

## Content

```
doc/                       Documentation folder
chess_merdia_unicode.ttf   The Chess Merida Unicode TrueType font
COPYING                    License information
FenTT.css                  The FenTT extension stylesheet
FenTT.php                  The FenTT extension code
Makefile                   Makefile to produce the documentation
Readme.md                  This file
```

## License

Copyright (C) 2007-2016  Michael Peeters `<https://github.com/xeyownt>`.

The FenTT MediaWiki extension comes with ABSOLUTELY NO WARRANTY. This is free software; you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

See file COPYING for more details.
