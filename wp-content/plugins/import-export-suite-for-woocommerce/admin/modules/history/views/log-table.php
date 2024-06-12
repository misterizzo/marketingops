<?php
/**
 * Log table view file
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$summary_item = '';
$summary      = array(
	'type'  => array(
		0 => array(
			'count'       => 0,
			'description' => __( 'Item with same ID already exists.' ),
			'error_code'  => 'already exists',
		),
		1 => array(
			'count'       => 0,
			'description' => __( 'Importing item conflicts with an existing post.' ),
			'error_code'  => 'conflicts with an existing post',
		),
		2 => array(
			'count'       => 0,
			'description' => __( 'Invalid product type.' ),
			'error_code'  => 'Invalid product type',
		),
		3 => array(
			'count'       => 0,
			'description' => __( 'Basic version does not support import of variable products.' ),
			'error_code'  => 'version does not support import',
		),
		4 => array(
			'count'       => 0,
			'description' => __( 'Invalid date format.' ),
			'error_code'  => 'Invalid date format',
		),
		5 => array(
			'count'       => 0,
			'description' => __( 'Unknown order status.' ),
			'error_code'  => 'Unknown order status',
		),
		6 => array(
			'count'       => 0,
			'description' => __( 'Cannot insert without coupon code.' ),
			'error_code'  => 'Cannot insert without',
		),
		7 => array(
			'count'       => 0,
			'description' => __( 'Invalid coupon discount type.' ),
			'error_code'  => 'Invalid discount type',
		),

	),
	'items' => array(
		'product' => 'https://www.webtoffee.com/products-troubleshooting-guide/',
		'user'    => 'https://www.webtoffee.com/users-troubleshooting-guide/',
		'order'   => 'https://www.webtoffee.com/orders-troubleshooting-guide/',
		'coupon'  => 'https://www.webtoffee.com/coupons-troubleshooting-guide/',
	),
);

if ( isset( $log_list ) && is_array( $log_list ) && count( $log_list ) > 0 ) {
	if ( 0 == $offset ) {
		?>
		<table class="wp-list-table widefat fixed striped log_view_tb" style="margin-bottom:25px;">
		<thead>
			<tr>
				<th style="width:15%"><?php esc_html_e( 'Row No' ); ?></th>
				<th style="width:15%"><?php esc_html_e( 'Status' ); ?></th>
				<th style="width:50%"><?php esc_html_e( 'Message' ); ?></th>
				<th style="width:20%"><?php esc_html_e( 'Item' ); ?></th>
			</tr>
		</thead>
		<tbody class="log_view_tb_tbody">
		<?php
	}

	foreach ( $log_list as $key => $log_item ) {
		if ( ! isset( $log_item['row'] ) ) {
			continue;
		}

		if ( ! $log_item['status'] ) {
			if ( strpos( $log_item['message'], 'already exists' ) !== false ) {
				$summary['type'][0]['count'] = ( $summary['type'][0]['count'] + 1 );
			}

			if ( strpos( $log_item['message'], 'conflicts with an existing post' ) !== false ) {
				$summary['type'][1]['count'] = ( $summary['type'][1]['count'] + 1 );
			}

			if ( strpos( $log_item['message'], 'Invalid product type' ) !== false ) {
				$summary['type'][2]['count'] = ( $summary['type'][2]['count'] + 1 );
			}

			if ( strpos( $log_item['message'], 'version does not support import' ) !== false ) {
				$summary['type'][3]['count'] = ( $summary['type'][3]['count'] + 1 );
			}

			if ( strpos( $log_item['message'], 'Invalid order date format' ) !== false ) {
				$summary['type'][4]['count'] = ( $summary['type'][4]['count'] + 1 );
			}

			if ( strpos( $log_item['message'], 'Unknown order status' ) !== false ) {
				$summary['type'][5]['count'] = ( $summary['type'][5]['count'] + 1 );
			}

			if ( strpos( $log_item['message'], 'Cannot insert without' ) !== false ) {
				$summary['type'][6]['count'] = ( $summary['type'][6]['count'] + 1 );
			}

			if ( strpos( $log_item['message'], 'Invalid coupon discount type' ) !== false ) {
				$summary['type'][7]['count'] = ( $summary['type'][7]['count'] + 1 );
			}


			if ( strpos( strtolower( $log_item['message'] ), 'product' ) !== false ) {
				$summary_item = 'product';
			}

			if ( strpos( strtolower( $log_item['message'] ), 'order' ) !== false ) {
				$summary_item = 'order';
			}

			if ( strpos( strtolower( $log_item['message'] ), 'coupon' ) !== false ) {
				$summary_item = 'coupon';
			}

			if ( strpos( strtolower( $log_item['message'] ), 'user' ) !== false ) {
					  $summary_item = 'user';
			}
		}//end if

		?>
		<tr>
			<td style="width:15%"><?php echo absint( $log_item['row'] ); ?></td>
			<td style="width:15%"><?php echo esc_html( $log_item['status'] ? __( 'Success' ) : __( 'Failed/Skipped' ) ); ?></td>
			<td style="width:50%"><?php echo esc_html( $log_item['message'] ); ?></td>
			<td style="width:20%">
		<?php
		if ( isset( $log_item['post_id'] ) ) {
			if ( $show_item_details ) {
				   $item_data = $item_type_module_obj->get_item_by_id( $log_item['post_id'] );
				if ( $item_data && isset( $item_data['title'] ) ) {
					if ( isset( $item_data['edit_url'] ) ) {
						echo wp_kses_post( '<a href="' . esc_url( $item_data['edit_url'] ) . '" target="_blank">' . $item_data['title'] . '</a>' );
					} else {
						  echo esc_html( $item_data['title'] );
					}
				} else {
					echo esc_html( $log_item['post_id'] );
				}
			} else {
						  echo esc_html( $log_item['post_id'] );
			}
		}//end if
		?>
			</td>
		</tr>
		<?php
	}//end foreach
	?>
				<div style="background-color: #f6f7f7;padding: 10px;">
			<?php

			foreach ( $summary['type'] as $summary_row ) {
				$summary_row_count = $summary_row['count'];
				if ( $summary_row_count ) :
					$summary_row_help_link = $summary['items'][ $summary_item ];
					?>
					<p><?php echo esc_html( $summary_row['description'] . "($summary_row_count)" ); ?> - <?php esc_html_e( 'Please refer' ); ?> <a href="<?php echo esc_url( $summary_row_help_link ); ?>" target="_blank"><?php esc_html_e( 'to this article' ); ?></a> <?php esc_html_e( 'for troubleshoot.' ); ?></p> 
					<?php
				endif;
			}
			?>
		</div>  
		<?php
		if ( 0 == $offset ) {
			?>
		</tbody>
		</table>
		<h4 style="margin-top:0px;"> 
			<a class="wt_iew_history_loadmore_btn button button-primary"> <?php esc_html_e( 'Load more.' ); ?></a>
			<span class="wt_iew_history_loadmore_loading" style="display:none;"><?php esc_html_e( 'Loading....' ); ?></span>
		</h4>
			<?php
		}
} else {
	?>
	<h4 style="margin-bottom:55px;"><?php esc_html_e( 'No records found.' ); ?> </h4>
	<?php
}//end if

