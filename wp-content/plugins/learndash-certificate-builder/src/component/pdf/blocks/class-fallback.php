<?php
/**
 * The default class for processing normal block
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks;

/**
 * Class Fallback
 *
 * @package LearnDash_Certificate_Builder\Component\Builder\Pdf\Blocks
 */
class Fallback extends Block {

	/**
	 * Trigger code
	 */
	public function run() {
		/**
		 * Filters the fallback output of a Block within the Certificate Builder.
		 *
		 * @since 1.1.2
		 *
		 * @param string                                                                                                                               $output The output for the current Block.
		 * @param array{id: string, blockName: string, attrs: array<string, mixed>, innerBlocks: array<string, mixed>[], innerHTML: string, innerContent: mixed[]} $block  The current Block.
		 *
		 * @return string
		 */
		$output = apply_filters(
			'learndash_certificate_builder_block_fallback',
			do_shortcode( trim( $this->block['innerHTML'] ) ),
			$this->block
		);

		$this->html->add( $output );
	}
}
