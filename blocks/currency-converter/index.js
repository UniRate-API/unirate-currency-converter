/* global wp */
( function () {
	var el               = wp.element.createElement;
	var __               = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var TextControl      = wp.components.TextControl;
	var SelectControl    = wp.components.SelectControl;
	var PanelBody        = wp.components.PanelBody;
	var RangeControl     = wp.components.RangeControl;

	wp.blocks.registerBlockType( 'unirate/currency-converter', {
		edit: function ( props ) {
			var attrs = props.attributes;
			var setAttr = props.setAttributes;

			return [
				el(
					InspectorControls,
					{ key: 'inspector' },
					el(
						PanelBody,
						{ title: __( 'Currency Settings', 'unirate-currency-converter' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Display style', 'unirate-currency-converter' ),
							value: attrs.displayStyle,
							options: [
								{ label: __( 'Interactive widget', 'unirate-currency-converter' ), value: 'widget' },
								{ label: __( 'Rate (e.g. 1 USD = 0.92 EUR)', 'unirate-currency-converter' ), value: 'rate' },
								{ label: __( 'Converted amount', 'unirate-currency-converter' ), value: 'convert' },
							],
							onChange: function ( val ) { setAttr( { displayStyle: val } ); },
						} ),
						el( TextControl, {
							label: __( 'From currency', 'unirate-currency-converter' ),
							value: attrs.from,
							onChange: function ( val ) { setAttr( { from: val.toUpperCase().slice( 0, 3 ) } ); },
						} ),
						el( TextControl, {
							label: __( 'To currency', 'unirate-currency-converter' ),
							value: attrs.to,
							onChange: function ( val ) { setAttr( { to: val.toUpperCase().slice( 0, 3 ) } ); },
						} ),
						el( RangeControl, {
							label: __( 'Amount', 'unirate-currency-converter' ),
							value: attrs.amount,
							min: 1,
							max: 10000,
							onChange: function ( val ) { setAttr( { amount: val } ); },
						} )
					)
				),
				el(
					'div',
					{ key: 'preview', className: 'unirate-block-editor-preview' },
					el( 'span', { className: 'unirate-block-editor-preview__icon' }, '💱' ),
					el( 'strong', null, __( 'UniRate Currency Converter', 'unirate-currency-converter' ) ),
					el(
						'span',
						{ className: 'unirate-block-editor-preview__pair' },
						attrs.amount + ' ' + attrs.from + ' → ' + attrs.to
					),
					el(
						'em',
						{ className: 'unirate-block-editor-preview__hint' },
						__( 'Live rate rendered on the frontend', 'unirate-currency-converter' )
					)
				),
			];
		},
	} );
} )();
