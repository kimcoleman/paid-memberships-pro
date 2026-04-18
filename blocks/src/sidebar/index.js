/**
 * Require Membership sidebar panel.
 */

import apiFetch from '@wordpress/api-fetch';
import { register } from '@wordpress/data';

function pmproCustomStore() {
	return {
		name: 'pmpro/require-membership',
		instantiate: () => {
			const listeners = new Set();
			const storeData = { restrictedLevels: [] };

			function storeChanged() {
				for ( const listener of listeners ) {
					listener();
				}
			}

			function subscribe( listener ) {
				listeners.add( listener );
				return () => listeners.delete( listener );
			}

			const selectors = {
				getRestrictedLevels() {
					return storeData['restrictedLevels'];
				},
			};

			const actions = {
				setRestrictedLevels( restrictedLevels ) {
					storeData['restrictedLevels'] = restrictedLevels;
					storeChanged();
				},fetchRestrictedLevels() {
					apiFetch( { path: 'pmpro/v1/post_restrictions/?post_id=' + pmpro_block_editor_sidebar.post_id } )
						.then( ( data ) => {
							// Set the restricted levels to the membership_id values.
							actions.setRestrictedLevels( data.map( ( item ) => item.membership_id ) );
							storeChanged();
						} )
						.catch( ( error ) => {
							console.error( error );
						} );
				},saveRestrictedLevels() {
					apiFetch( {
						path: 'pmpro/v1/post_restrictions/',
						method: 'POST',
						data: {
							post_id: pmpro_block_editor_sidebar.post_id,
							level_ids: storeData['restrictedLevels'],
						},
					} );
				}
			};
			actions.fetchRestrictedLevels();

			return {
				getSelectors: () => selectors,
				getActions: () => actions,
				subscribe,
			};
		},
	};
}
register( pmproCustomStore() );

( function ( wp ) {
	const { __ } = wp.i18n;
	const { registerPlugin } = wp.plugins;
	// Below is copied from WP 6.6 release notes for compatibility with pre-6.6 versions. This should be simplified when minimum WP version is 6.6.
	// https://make.wordpress.org/core/2024/06/18/editor-unified-extensibility-apis-in-6-6/
	const PluginDocumentSettingPanel = wp.editor?.PluginDocumentSettingPanel ?? ( wp.editPost?.PluginDocumentSettingPanel ?? wp.editSite?.PluginDocumentSettingPanel );
	const { Component } = wp.element;
	const { useState } = wp.element;
	const { Spinner, CheckboxControl, Button, __experimentalHStack as HStack } = wp.components;

	const { withSelect, withDispatch, dispatch } = wp.data;
	const { compose } = wp.compose;

	const RequireMembershipControl = compose(
		withDispatch( function ( dispatch, props ) {
			return {
				setRestrictedLevelsValue: function ( value ) {
					dispatch( 'pmpro/require-membership' ).setRestrictedLevels( value );
					// Add another action to update a fake meta value to force the save button to enable.
					dispatch( 'core/editor' ).editPost( { meta: { pmpro_force_save_enable: '1' } } );
				},
			};
		} ),
		withSelect( function ( select, props ) {
			return {
				restrictedLevels: select( 'pmpro/require-membership' ).getRestrictedLevels(),
			};
		} )
	)( function ( props ) {
		const groups = pmpro_block_editor_sidebar.level_groups || [];
		const [ restrictMode, setRestrictMode ] = useState( 'level' );

		// Switch mode and clear current selections.
		const switchMode = ( newMode ) => {
			if ( newMode !== restrictMode ) {
				props.setRestrictedLevelsValue( [] );
				setRestrictMode( newMode );
			}
		};

		// Build group checkboxes.
		const group_checkboxes = groups.map( ( group ) => {
			const groupLevelIds = group.level_ids || [];
			const allChecked = groupLevelIds.length > 0 && groupLevelIds.every(
				( id ) => props.restrictedLevels.includes( id )
			);
			return (
				<CheckboxControl
					__nextHasNoMarginBottom
					key={ 'group-' + group.value }
					label={ group.label }
					checked={ allChecked }
					onChange={ () => {
						let newValue = [ ...props.restrictedLevels ];
						if ( allChecked ) {
							// Remove all levels in this group.
							newValue = newValue.filter( ( id ) => ! groupLevelIds.includes( id ) );
						} else {
							// Add all levels in this group.
							groupLevelIds.forEach( ( id ) => {
								if ( ! newValue.includes( id ) ) {
									newValue.push( id );
								}
							} );
						}
						props.setRestrictedLevelsValue( newValue );
					} }
				/>
			);
		} );

		const level_checkboxes = props.levels.map(
			( level ) => {
				return (
					<CheckboxControl
						__nextHasNoMarginBottom
						key={ level.id }
						label={ level.name }
						checked={ props.restrictedLevels.includes( level.id ) }
						onChange={ () => {
							let newValue = [...props.restrictedLevels];
							if ( newValue.includes( level.id ) ) {
								newValue = newValue.filter(
									( item ) => item !== level.id
								);
							} else {
								newValue.push( level.id )
							}
							props.setRestrictedLevelsValue( newValue );
						} }
					/>
				)
			}
		);

		return (
			<>
				{ groups.length > 0 && (
					<>
						<HStack>
							<Button
								__next40pxDefaultSize
								variant={ restrictMode === 'group' ? 'primary' : 'secondary' }
								style={ { flexGrow: '1', justifyContent: 'center' } }
								onClick={ () => switchMode( 'group' ) }
							>
								{ __( 'By Group', 'paid-memberships-pro' ) }
							</Button>
							<Button
								__next40pxDefaultSize
								variant={ restrictMode === 'level' ? 'primary' : 'secondary' }
								style={ { flexGrow: '1', justifyContent: 'center' } }
								onClick={ () => switchMode( 'level' ) }
							>
								{ __( 'By Level', 'paid-memberships-pro' ) }
							</Button>
						</HStack>
						<br />
					</>
				) }
				{ restrictMode === 'group' && (
					<>
						{ groups.length > 1 &&
							<p> { __( 'Select', 'paid-memberships-pro' ) + ': ' }
								<button className="button-link" onClick={ () => {
									// Select all levels from all groups.
									const allLevelIds = groups.reduce( ( acc, group ) => {
										( group.level_ids || [] ).forEach( ( id ) => {
											if ( ! acc.includes( id ) ) acc.push( id );
										} );
										return acc;
									}, [] );
									props.setRestrictedLevelsValue( allLevelIds );
								} }>{ __( 'All', 'paid-memberships-pro' ) }</button>{ ' | ' }
								<button className="button-link" onClick={ () => {
									props.setRestrictedLevelsValue( [] );
								} }>{ __( 'None', 'paid-memberships-pro' ) }</button>
							</p>
						}
						<div className="pmpro-block-inspector-membershiplevels">
							{ group_checkboxes }
						</div>
					</>
				) }
				{ restrictMode === 'level' && (
					<>
						{
							// Add buttons to select all or none.
							level_checkboxes.length > 1 &&
							<p> { __( 'Select', 'paid-memberships-pro' ) + ': ' }
								<button className="button-link" onClick={ () => {
									props.setRestrictedLevelsValue( props.levels.map( ( level ) => level.id ) );
								} }>{ __( 'All', 'paid-memberships-pro' ) }</button>{ ' | ' }
								<button className="button-link" onClick={ () => {
									props.setRestrictedLevelsValue( [] );
								} }>{__( 'None', 'paid-memberships-pro' ) }</button>
							</p>
						}
						{
							level_checkboxes.length > 6 ? (
								<div className="pmpro-block-inspector-scrollable">
									{ level_checkboxes }
								</div>
							) : (
								<div className="pmpro-block-inspector-membershiplevels">
									{ level_checkboxes }
								</div>
							)
						}
					</>
				) }
			</>
		);
	} );

	// Whenever a post is saved, call the saveRestrictedLevels action.
	// Adapted from here to ensure API is only called once: https://github.com/WordPress/gutenberg/issues/17632#issuecomment-819379829
	/**
	 * Consults values to determine whether the editor is busy saving a post.
	 * Includes checks on whether the save button is busy.
	 * 
	 * @returns {boolean} Whether the editor is on a busy save state.
	 */
	function isSavingPost() {

		// State data necessary to establish if a save is occurring.
		const isSaving = wp.data.select('core/editor').isSavingPost() || wp.data.select('core/editor').isAutosavingPost();
		const isSaveable = wp.data.select('core/editor').isEditedPostSaveable();
		const isPostSavingLocked = wp.data.select('core/editor').isPostSavingLocked();
		const hasNonPostEntityChanges = wp.data.select('core/editor').hasNonPostEntityChanges();
		const isAutoSaving = wp.data.select('core/editor').isAutosavingPost();
		const isButtonDisabled = isSaving || !isSaveable || isPostSavingLocked;
	
		// Reduces state into checking whether the post is saving and that the save button is disabled.
		const isBusy = !isAutoSaving && isSaving;
		const isNotInteractable = isButtonDisabled && ! hasNonPostEntityChanges;
		
		return isBusy && isNotInteractable;
	}
	
	// Current saving state. isSavingPost is defined above.
	var wasSaving = isSavingPost();
	wp.data.subscribe( function () {
		// New saving state
		let isSaving = isSavingPost();

		// It is done saving if it was saving and it no longer is.
		let isDoneSaving = wasSaving && !isSaving;
	  
		// Update value for next use.
		wasSaving = isSaving;
		if ( isDoneSaving ) {
			dispatch( 'pmpro/require-membership' ).saveRestrictedLevels();
		}
	} );

	class PMProSidebar extends Component {
		constructor( props ) {
			super( props );
			this.state = {
				levelList: [],
				loadingLevels: true,
			};
		}

		componentDidMount() {
			this.fetchlevels();
		}

		fetchlevels() {
			apiFetch( {
				path: 'pmpro/v1/membership_levels',
			} ).then( ( data ) => {
				// If data is an object, convert to associative array
				if (typeof data === 'object') {
					data = Object.keys(data).map(function(key) {
						return data[key];
					});
				}
				this.setState( {
					levelList: data,
					loadingLevels: false,
				} );
			} ).catch( ( error ) => {
				this.setState( {
					levelList: error,
					loadingLevels: false,
				} );
			} );
		}

		render() {
			var sidebar_content = <Spinner />;
			if ( ! this.state.loadingLevels ) {
				if ( ! Array.isArray( this.state.levelList ) ) {
					sidebar_content = <p>{ __('Error retrieving membership levels.', 'restrict-with-stripe') + ' ' + this.state.levelList }</p>;
				} else if ( this.state.levelList.length === 0 ) {
					sidebar_content = <p>{ __('No levels found. Please create a level to restrict content.', 'paid-memberships-pro') }</p>;
				} else {
					sidebar_content = <div>
						<RequireMembershipControl
							label={ __( 'Membership Levels', 'paid-memberships-pro' ) }
							levels={ this.state.levelList }
						/>
					</div>;
				}
			}

			return (
				<PluginDocumentSettingPanel name="pmpro-sidebar-panel" title={ __( 'Require Membership', 'paid-memberships-pro' ) } >
					{sidebar_content}
				</PluginDocumentSettingPanel>
			);
		}
	}

	registerPlugin( 'pmpro-sidebar', {
		icon: 'lock',
		render: PMProSidebar,
	} );
} )( window.wp );
