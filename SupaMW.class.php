<?php
/**
 * SupaMW.body.php -- SUPA java applet support for uploading images
 * from clipboard directly into MediaWiki
 * Copyright 2011+ Vitaliy Filippov <vitalif@mail.ru>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file
 * @ingroup Extensions
 * @author Vitaliy Filippov <vitalif@mail.ru>
 */

// Really just a Base64 source
class UploadFromSupa extends UploadBase {
    function initializeFromRequest( &$request ) {
        global $wgTmpDirectory;
        $desiredDestName = $request->getText( 'wpDestFile' );
        $content = base64_decode( $request->getVal( 'wpSupaContent' ) );
        $localFile = tempnam( $wgTmpDirectory, 'SUPA' );
        file_put_contents( $localFile, $content );
        return $this->initializePathInfo(
            $desiredDestName,
            $localFile,
            strlen( $content )
        );
    }
    function initialize( $name, $tempPath, $fileSize ) {
        return $this->initializePathInfo( $name, $tempPath, $fileSize );
    }
    static function isValidRequest( $request ) {
        return (bool)$request->getVal( 'wpSupaContent' );
    }
}

class SupaMW {
    static function uploadForm( &$descriptor, &$radio, $selectedSourceType ) {
        global $wgLang;
        // Determine file size limit
        // Since we are uploading a file through an <input type=hidden>,
        // suhosin limits are also applied in addition to post_max_size.
        $limit1 = wfShorthandToInteger( ini_get( 'suhosin.request.max_value_length' ) );
        $limit2 = wfShorthandToInteger( ini_get( 'suhosin.post.max_value_length' ) );
        $limit3 = wfShorthandToInteger( ini_get( 'post_max_size' ) );
        if ( $limit1 > 0 && $limit1 < $limit3 ) {
            $limit3 = $limit1;
        }
        if ( $limit2 > 0 && $limit2 < $limit3 ) {
            $limit3 = $limit2;
        }
        $radio = true;
        $descriptor['UploadFileSUPA'] = array(
            'class' => 'SUPAField',
            'section' => 'source',
            'id' => 'wpUploadFileSUPA',
            'label-message' => 'supa-source',
            'upload-type' => 'Supa',
            'radio' => &$radio,
            'help' => wfMsgExt( 'upload-maxfilesize',
                    array( 'parseinline', 'escapenoentities' ),
                    $wgLang->formatSize( $limit3 / 1.37 )
                ),
            'checked' => $selectedSourceType == 'url',
        );
        return true;
    }
    static function addHandler( $type, &$className ) {
        UploadBase::$uploadHandlers[] = 'Supa';
        return true;
    }
}

class SUPAField extends HTMLTextField {
    function getLabelHtml( $attr = array() ) {
        $id = "wpSourceType{$this->mParams['upload-type']}";
        $label = Html::rawElement( 'label', array( 'for' => $id ), $this->mLabel );

        if ( !empty( $this->mParams['radio'] ) ) {
            $attribs = array(
                'name' => 'wpSourceType',
                'type' => 'radio',
                'id' => $id,
                'value' => $this->mParams['upload-type'],
            );
            if ( !empty( $this->mParams['checked'] ) )
                $attribs['checked'] = 'checked';
            $label .= Html::element( 'input', $attribs );
        }

        return Html::rawElement( 'td', array( 'class' => 'mw-label' ), $label );
    }
    function getInputHTML( $value ) {
        global $wgOut;
        $wgOut->addModules( 'ext.SupaMW' );
        return '<input type="hidden" name="wpSupaContent" id="wpSupaContent" value="" />
<div id="mw-supa-placeholder"><div id="wpUploadFileSUPA"></div>'.wfMsgNoTrans( 'supa-needs-java' ).'</div>';
    }
}
