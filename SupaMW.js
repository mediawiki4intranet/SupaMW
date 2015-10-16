if ( navigator.javaEnabled() ) {
    window.supaPostApplet = function() {
        document.getElementById( 'mw-supa-placeholder' ).innerHTML =
'<a href="javascript:void(0)" onclick="supaPasteAgain()">' + mw.msg( 'supa-paste-again' ) + '</a><br />\
<applet id="SupaApplet" archive="' + mw.config.get('wgScriptPath') + '/extensions/SupaMW/Supa.jar"\
    code="de.christophlinder.supa.SupaApplet" width="400" height="300"\
    style="border: 3px solid #ddd">\
    <param name="trace" value="true" />\
    <param name="pasteonload" value="true" />\
    <param name="clickforpaste" value="true" />\
    <param name="imagecodec" value="png" />\
    <param name="encoding" value="base64" />\
    <param name="previewscaler" value="fit to canvas" />\
    ' + mw.msg( 'supa-needs-java' ) + '\
</applet>';
        var d = document.getElementById( 'wpDestFile' );
        var s = d.value;
        var p = s.lastIndexOf( '.' );
        if ( p > -1 ) {
            s = s.substr( 0, p );
        }
        if ( !s ) {
            s = new Date();
            s = [ 'Screenshot', s.getFullYear(), ''+s.getMonth(), ''+s.getDate() ];
            if ( s[1].length < 2 ) s[1] = '0'+s[1];
            if ( s[2].length < 2 ) s[2] = '0'+s[2];
            s = s.join( '-' );
        }
        d.value = s + '.png';
        return true;
    };
    $( '#wpSourceTypeSupa' ).change( supaPostApplet );
    $( window ).load( function() {
        document.getElementById( 'wpSourceTypeSupa' ).disabled = false;
        if ( document.getElementById( 'wpSourceTypeSupa' ).checked ) {
            // Workaround Firefox's form value restoring
            supaPostApplet();
        }
        return true;
    });
    document.getElementById( 'mw-upload-form' ).onsubmit = function() {
        var e = document.getElementById( 'wpSourceTypeSupa' );
        if ( e && e.checked ) {
            var s;
            try {
                s = document.getElementById( 'SupaApplet' ).getEncodedString();
            } catch( e ) {
                supaError();
                return false;
            }
            if ( !s ) {
                alert( mw.msg( 'supa-empty-content' ) );
                return false;
            }
            document.getElementById( 'wpSupaContent' ).value = s;
        }
        return true;
    };
    window.supaError = function() {
        alert( mw.msg( 'supa-java-disabled' ) );
        document.getElementById( 'mw-supa-placeholder' ).innerHTML = mw.msg( 'supa-java-disabled' );
        document.getElementById( 'wpSourceTypeFile' ).checked = true;
        document.getElementById( 'wpSourceTypeSupa' ).disabled = true;
    };
    window.supaPasteAgain = function() {
        try {
            document.getElementById( 'SupaApplet' ).pasteFromClipboard();
        } catch( e ) {
            supaError();
        }
    };
}
