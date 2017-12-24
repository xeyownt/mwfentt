<?php

/*==============================================================================
 * Mediawiki PHP extension for chess FEN diagrams rendering
 *
 * Copyright (C) 2007-2016  Michael Peeters <https://github.com/xeyownt>
 *
 * This file is part of the FenTT MediaWiki extension
 * <http://www.mediawiki.org/wiki/Extension:FenTT>.
 *
 * The FenTT MediaWiki extension is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * The FenTT MediaWiki extension is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 *==============================================================================
 */

if ( function_exists( 'wfLoadExtension' ) ) {
    wfLoadExtension( 'FenTT' );
    wfWarn(
           'Deprecated PHP entry point used for FenTT extension. Please use wfLoadExtension ' .
           'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
    );
    return true;
} else {
    $wgHooks['ParserFirstCallInit'][] = 'FenTTHooks::onParserFirstCallInit';

    $wgResourceModules['ext.FenTT.styles'] = array(
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'FenTT',
        'styles'        => 'FenTT.css',
        'position'      => 'top',
    );

    $wgExtensionCredits['parserhook'][] = array(
        'name'        => 'FenTT',
        'version'     => '1.0.3',
        'license-name'=> 'GPL-2.0+',
        'author'      => 'Michaël Peeters',
        'url'         => 'http://www.mediawiki.org/wiki/Extension:FenTT',
        'description' => 'Render high-quality chess diagrams in FEN notation using TrueType fonts and CSS style.'
    );

    $wgAutoloadClasses['FenTTHooks'] = __DIR__ . '/FenTT.hooks.php';
}

?>
