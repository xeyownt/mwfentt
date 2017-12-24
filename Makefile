EXT := FenTT
FENTT_SRC := $(EXT).hooks.php

DOC_SRC   := doc/reference.mw
DOC_PHP  := $(DOC_SRC:%.mw=%.php)
DOC_HTML  := $(DOC_SRC:%.mw=%.html)

.PHONY: all
all:
	@echo "FenTT Mediawiki Extension makefile"
	@echo
	@echo "Available targets:"
	@echo "    make doc     : generate documentation."
	@echo "    make install : Install as Mediawiki extension (WILL DELETE NON-NECESSARY FILES)."
	@echo
	@echo "For documentation, run 'make doc' and open file doc/reference.html in a browser."

.PHONY: clean
clean:
	rm $(DOC_PHP)
	rm $(DOC_HTML)
	rm doc/FenTT.css
	rm doc/chess_merida_unicode.ttf

# Our almighty sed script to translate wiki text tag <fentt> in a php call ;-)
$(DOC_HTML): %.html: %.mw $(FENTT_SRC) Makefile
	@sed -rn '/<fentt/{:a /<\/fentt>/!{N;b a}; s/<fentt([^>]*)>([^<]*)<\/fentt>/<?php print FenTT::renderFentt("\2", array(\1)); ?>/;s/(id|border|mode|style|class) *= *"([^"]*)"/"\1"=>"\2",/g;s/, *\)/)/};p' $< > $*.php
	@php $*.php > $@

# Must copy FenTT.css and ttf fonts because firefox refuses to load web fonts up tree for security reasons
.PHONY: doc
doc: $(DOC_HTML)
	cp FenTT.css doc/
	cp chess_merida_unicode.ttf doc/

.PHONY: install
install:
	rm -rf .git COPYING doc Makefile README.md
	egrep -q "^[[:blank:]]*wfLoadExtension[[:blank:]]*\([[:blank:]]*'$(EXT)'[[:blank:]]*\)[[:blank:]]*;[[:blank:]]*$$" ../../LocalSettings.php || sed -ri "\$$s/$$/\nwfLoadExtension( '$(EXT)' );/" ../../LocalSettings.php
