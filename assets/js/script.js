jQuery( function( $ ) {

    // wcb_params is required to continue, ensure the object exists
    if ( typeof wcb_params === 'undefined' ) {
        return false;
    }

    var wcb = {
        label: false,
        labelSetXml: false,
        labelSize: false,
        $printerList: $( '#woocommerce_product_barcodes_label_size' ),
        init: function() {
            if ( wcb_params.debug ) {
                dymo.label.framework.trace = 1;
            }

            dymo.label.framework.init( this.events );

            // Change preview label size
            $( document ).on( 'change', '#woocommerce_product_barcodes_label_size', this.labelPreview );
            // Update label preview
            $( document ).on( 'change', '.label-preview-option', this.updatePreview );
            // Update print button
            $( document ).on( 'change keyup', '.product-label-input', this.updatePrintButton );
            // Print labels
            $( document ).on( 'click', '#wcb_print', this.printLabels );
        },
        events: function() {
            wcb.checkEnvironment();
            wcb.loadLabel();
            wcb.loadPrinters();
        },
        checkEnvironment: function() {
            var environment = dymo.label.framework.checkEnvironment();
            if ( wcb_params.debug ) {
                console.log( 'dymo environment', environment );
            }
            if ( !environment.isFrameworkInstalled || !environment.isBrowserSupported || !environment.isWebServicePresent ) {
                if ( $( document.body ).hasClass( 'product_page_product_barcodes' ) ) {
                    if ( $( '.wcb-error' ).length === 0 ) {
                        $( '.wrap' ).find( 'h2' ).before( '<div class="error wcb-error"><p>' + environment.errorDetails + ' ' + wcb_params.i18n_need_help + '</p></div>' );
                    }
                }
            }
        },
        loadLabel: function() {
            wcb.labelSize = wcb_params.label_size;

            if ( wcb.$printerList.length ) {
                wcb.labelSize = wcb.$printerList.val();
            }

            wcb.getLabel( wcb.labelSize );
        },
        loadPrinters: function() {
            dymo.label.framework.getLabelWriterPrintersAsync().then( function( printers ) {

                if ( typeof printers === 'undefined' || printers.length === 0 ) {
                    return;
                }

                if ( wcb_params.debug ) {
                    console.log( 'dymo printers', printers );
                }

                for ( var i = 0; i < printers.length; ++i ) {
                    var printer = printers[ i ];

                    $( '<option>' ).val( printer.name ).text( printer.name ).appendTo( '#woocommerce_product_barcodes_dymo_printer' );
                    // When appended choose first option.
                    $( '#woocommerce_product_barcodes_dymo_printer' ).find( 'option' ).eq( i + 1 ).prop( 'selected', true );
                }
            } );
        },
        getLabel: function( size ) {
            var labelURL = wcb_params.plugin_url + '/assets/labels/' + size + '.label?v=' + Math.random() * 10;
            dymo.label.framework.openLabelFileAsync( labelURL ).then( function( labelXml ) {
                wcb.label = dymo.label.framework.openLabelXml( labelXml.getLabelXml() );
                wcb.renderLabel( size );
                wcb.printPreview();
            } );
        },
        printLabel: function( labelSetXml ) {
            var printers = dymo.label.framework.getLabelWriterPrintersAsync();
            var environment = dymo.label.framework.checkEnvironment();
            var printer = $( '#woocommerce_product_barcodes_dymo_printer' ).val();

            try {
                if ( !environment.isFrameworkInstalled || !environment.isBrowserSupported ) {
                    throw environment.errorDetails;
                }
                if ( printers.length === 0 ) {
                    throw wcb_params.i18n_no_printers_error;
                }
                if ( '' === printer ) {
                    throw wcb_params.i18n_no_printers_error;
                }
                if ( false === wcb.label ) {
                    throw wcb_params.i18n_label_loaded_error;
                }
                if ( false === wcb.labelSetXml ) {
                    throw wcb_params.i18n_data_loaded_error;
                }
                // Print label
                dymo.label.framework.printLabelAsync( printer, null, wcb.label, labelSetXml );
            } catch ( e ) {
                alert( e.message || e );
            }
        },
        createLabelSet: function() {
            var labelSetXml = new dymo.label.framework.LabelSetBuilder();

            $( '.wcb_barcodes' ).each( function( index, value ) {
                var $variation = $( this ),
                    name = $variation.find( 'input.product-name' ).val(),
                    barcode = $variation.find( 'input.product-barcode' ).val(),
                    metadata = $variation.find( 'input.product-metadata' ).val(),
                    amount = $variation.find( 'input.product-label-input' ).val();

                for ( var i = 0; i < parseInt( amount ); i++ ) {
                    var record = labelSetXml.addRecord();
                    record.setText( 'product_name', name );
                    record.setText( 'metadata', $.trim( metadata ) );
                    record.setText( 'barcode', barcode );
                }
            } );

            return labelSetXml;
        },
        printPreview: function() {
            if ( wcb.label ) {
                dymo.label.framework.renderLabelAsync( wcb.label ).then( function( pngData ) {
                    var previewImg = $( '<img />', {
                        src: 'data:image/png;base64,' + pngData
                    } );
                    //$( '#woocommerce-dymo-print-preview-img' ).show().attr( 'src', 'data:image/png;base64,' + pngData );
                    $( '#woocommerce-dymo-print-preview' ).html( previewImg );
                } );
            }
        },
        renderLabel: function( size ) {
            var metadata, name, price, barcode;
            metadata = $( '.metadata:checked' ).map( function() {
                return $.trim( $( this ).parent().text() );
            } ).get().join( ' ' );

            name = $( '.name:checked' ).map( function() {
                return $.trim( $( this ).parent().text() );
            } ).get().join( ' ' );

            price = $( '.price:checked' ).map( function() {
                return $.trim( $( this ).parent().text() );
            } ).get().join( ' ' );

            barcode = $( '.barcode:checked' ).map( function() {
                return $.trim( '1234' );
            } ).get().join( ' ' );

            if ( 'small' === size ) {
                wcb.label.setObjectText( 'price', price );
            } else {
                wcb.label.setObjectText( 'metadata', metadata );
                wcb.label.setObjectText( 'product_name', name );
            }

            wcb.label.setObjectText( 'barcode', barcode );
        },
        labelPreview: function() {
            wcb.labelSize = $( this ).val();
            wcb.getLabel( wcb.labelSize );
        },
        updatePreview: function() {
            wcb.renderLabel( wcb.labelSize );
            wcb.printPreview();
        },
        updatePrintButton: function() {
            var barcodes = 0,
                $printButton = $( '#wcb_print' );

            $( '.product-label-input' ).each( function() {
                barcodes += Number( $( this ).val() );
            } );

            if ( barcodes > 0 ) {
                $printButton.prop( 'disabled', false ).find( 'span' ).text( barcodes );
            } else {
                $printButton.prop( 'disabled', true ).find( 'span' ).text( '' );
            }
        },
        printLabels: function( e ) {
            e.preventDefault();
            wcb.labelSetXml = wcb.createLabelSet();
            wcb.printLabel( wcb.labelSetXml );
        }
    };

    $( window ).on( 'load', wcb.init() );

} );
