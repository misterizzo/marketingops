/**
 * WordPress dependencies
 */
import { combineReducers, createReduxStore, register } from '@wordpress/data'

/**
 * Internal dependencies
 */
import * as actions from './actions'
import * as reducers from './reducers'
import * as selectors from './selectors'

const store = register(
	createReduxStore( 'rank-math-pro-seo-analysis', {
		reducer: combineReducers( reducers ),
		selectors,
		actions,
	} )
)

export function getStore() {
	return store
}
