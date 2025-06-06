<?php
/**
 * Miscellaneous admin related functionality.
 *
 * @since      1.0
 * @package    RankMathPro
 * @subpackage RankMathPro\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMathPro\Admin;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;
use RankMathPro\Admin\Admin_Helper as ProAdminHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Misc admin class.
 *
 * @codeCoverageIgnore
 */
class Misc {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function __construct() {
		$this->action( 'cmb2_default_filter', 'change_fk_default', 20, 2 );
		$this->action( 'rank_math/pro_badge', 'header_pro_badge' );
	}

	/**
	 * Add options to Image SEO module.
	 *
	 * @param mixed  $defaults Default value.
	 * @param object $field   Field object.
	 */
	public function change_fk_default( $defaults, $field ) {
		if ( 'rank_math_focus_keyword' !== $field->id() ) {
			return $defaults;
		}

		if ( ! Admin_Helper::is_term_edit() ) {
			return $defaults;
		}

		return $this->get_term();
	}

	/**
	 * Get term.
	 *
	 * @return string
	 */
	public function get_term() {
		global $tag;
		if ( isset( $tag->name ) ) {
			return $tag->name;
		}

		return '';
	}

	/**
	 * Check and print the license type as a badge in the header of Rank Math's setting pages.
	 */
	public static function header_pro_badge() {
		$plan = ProAdminHelper::get_plan();
		echo '<span class="rank-math-pro-badge ' . esc_attr( $plan ) . '">' . esc_html( $plan ) . '</span>';
	}
}
