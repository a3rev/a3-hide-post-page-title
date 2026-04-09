( function ( wp ) {
	var el            = wp.element.createElement;
	var useSelect     = wp.data.useSelect;
	var useDispatch   = wp.data.useDispatch;
	var PluginDocumentSettingPanel = wp.editor.PluginDocumentSettingPanel;
	var CheckboxControl = wp.components.CheckboxControl;
	var registerPlugin  = wp.plugins.registerPlugin;

	var cfg     = typeof a3HpptEditorSidebar !== 'undefined' ? a3HpptEditorSidebar : {};
	var metaKey = cfg.metaKey || '_a3hpt_headertitle';
	var i18n    = cfg.i18n || {};

	function HpptPanel() {
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		var editPost = useDispatch( 'core/editor' ).editPost;

		var raw = meta[ metaKey ];
		var checked = raw === true || raw === 1 || raw === '1';

		return el( PluginDocumentSettingPanel, {
			name: 'a3-hppt-hide-title',
			title: i18n.panelTitle,
			className: 'a3-hppt-editor-sidebar',
		},
			el( CheckboxControl, {
				label: i18n.checkboxLabel,
				checked: checked,
				onChange: function ( v ) {
					var patch = {};
					patch[ metaKey ] = v ? true : false;
					editPost( { meta: patch } );
				},
			} )
		);
	}

	registerPlugin( 'a3-hppt-editor-sidebar', {
		render: HpptPanel,
		icon: 'hidden',
	} );
} )( window.wp );
