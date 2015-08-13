( function( $ ) {
    var label, labelSet, labelSize,
        stock = 1;

    /**
     * init
     */
    function init() {
        loadLabel();
        loadPrinters();
    }

    /**
     * load dymo label data
     */
    function loadLabel() {
        var labelSize = wcb_params.label_size,
            $printerList = $( '#woocommerce_product_barcodes_label_size' );

        if ( $printerList.length ) {
            labelSize = $printerList.val();
        }

        getLabel( labelSize );
    }

    /**
     * get list of installed dymo label printers
     * @return string
     */
    function loadPrinters() {
        var $printerList = $( '#woocommerce_product_barcodes_dymo_printer' ),
            printers = dymo.label.framework.getLabelWriterPrinters();

        if ( typeof printers === 'undefined' ) {
            alert( wcb_params.no_prints_error );
            return;
        }

        for ( var i = 0; i < printers.length; ++i ) {
            var printer = printers[ i ],
                printerName = printer.name,

                option = $( '<option>' ).val( printerName ).text( printerName );
            $printerList.append( option );
        }
    }

    /**
     * get label data
     * @param  {string} size
     */
    function getLabel( size ) {
        $.get( wcb_params.plugin_url + '/assets/labels/' + size + '.label', function( labelXml ) {
            label = dymo.label.framework.openLabelXml( labelXml );
            renderLabel();
            printPreview();
        }, "text" );
    }

    /**
     * print label
     * @param  {object} data
     * @param  {object} event
     * @return {void}
     */
    function printLabel( data, event ) {
        try {
            if ( ! label ) {
                throw wcb_params.label_loaded_error;
            }
            if ( ! labelSet ) {
                throw wcb_params.data_loaded_error;
            }
            label.print( wcb_params.dymo_printer, null, data );
        } catch ( event ) {
            alert( event.message || event );
        }
    }

    function createLabelSet() {
        var labelSet = new dymo.label.framework.LabelSetBuilder();

        $( '.wcb_barcodes' ).each( function( index, value ) {
            var $variation = $( this ),
                name = $variation.find( 'input.product-name' ).val(),
                barcode = $variation.find( 'input.product-barcode' ).val(),
                metadata = $variation.find( 'input.product-metadata' ).val(),
                stock = $variation.find( 'input.product-label-input' ).val();

            for ( var i = 0; i < stock; i++ ) {
                var record = labelSet.addRecord();
                record.setText( 'product_name', name );
                record.setText( 'metadata', $.trim( metadata ) );
                record.setText( 'product_barcode', barcode );
            }
        } );

        return labelSet;
    }

    function printPreview() {
        var $preview = $( '#woocommerce-dymo-print-preview-img' ),
            pngData = label.render();
        $preview.attr( 'src', 'data:image/png;base64,' + pngData );
    }

    function renderLabel() {
        var metadata = $( '.metadata:checked' ).map( function() {
            return $.trim( $( this ).parent().text() );
        } ).get().join( ' ' );

        var name = $( '.name:checked' ).map( function() {
            return $.trim( $( this ).parent().text() );
        } ).get().join( ' ' );

        var barcode = $( '.barcode:checked' ).map( function() {
            return $.trim( '1234' );
        } ).get().join( ' ' );

        label.setObjectText( 'metadata', metadata );
        label.setObjectText( 'product_name', name );
        label.setObjectText( 'product_barcode', barcode );
    }

    // load label and defaults
    $( window ).on( 'load', init );

    $( document ).on( 'change', '#woocommerce_product_barcodes_label_size', function() {
        var labelSize = $( this ).val();
        getLabel( labelSize );
    } );

    // update label preview
    $( document ).on( 'change', '.label-preview-option', function() {
        renderLabel();
        printPreview();
    } );

    // update print button
    $( document ).on( 'change keyup', '.product-label-input', function() {
        var sum = 0,
            $printButton = $( '#wcb_print_btn' );

        $( '.product-label-input' ).each( function() {
            sum += Number( $( this ).val() );
            if ( wcb_params.dymo_printer ) {
                if ( sum > 0 ) {
                    $printButton.prop( 'disabled', false ).find( 'span' ).text( sum );
                } else {
                    $printButton.prop( 'disabled', true ).find( 'span' ).text( '' );
                }
            }
        } );
    } );

    // print
    $( document ).on( 'click', '#wcb_print_btn', function( e ) {
        event.preventDefault();
        labelSet = createLabelSet();
        printLabel( labelSet, e );
        return false;
    } );

} )( jQuery );
