<?php
/**
 * Import evaluator section of the plugin
 *
 * @link
 *
 * @package ImportExportSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Wt_Evaluator Class.
 */
class Wt_Evaluator {


	/**
	 * Operators by order of precedence and with their arity
	 *
	 * @var string
	 */
	private $operators = array(
		'!'  => 1,
		'/'  => 2,
		'*'  => 2,
		'-'  => 2,
		'+'  => 2,
		'<'  => 2,
		'>'  => 2,
		'<=' => 2,
		'>=' => 2,
		'&&' => 2,
		'||' => 2,
		'!=' => 2,
		'==' => 2,
	);

	/**
	 * Constructor
	 *
	 * @param string $expression Expression.
	 */
	public function __construct( $expression ) {
		$number   = '/\b\d+(?:\.\d+)?\b/';
		$variable = '/\$\w+/';
		$operator = '/[\!&\|+\-<>=\\/\*]+/';

		$numbers = array();
		preg_match_all( $number, $expression, $numbers );
		$numbers = $numbers[0];

		$variables = array();
		preg_match_all( $variable, $expression, $variables );
		$variables = $variables[0];

		$operators = array();
		preg_match_all( $operator, $expression, $operators );
		$operators = $operators[0];

		$expression = preg_replace( $variable, 'v', $expression );
		$expression = preg_replace( $number, 'n', $expression );
		$expression = preg_replace( $operator, 'o', $expression );

		$nodes = array();
		$group = &$nodes;
		$stack = array();
		$expression_len = strlen( $expression );
		for ( $i = 0; $i < $expression_len; ++$i ) {
			if ( 'v' == $expression[ $i ] ) {
				$group[] = array(
					'type' => 'variable',
					'value' => array_shift( $variables ),
				);
			} else if ( 'n' == $expression[ $i ] ) {
				$group[] = array(
					'type' => 'number',
					'value' => (float) array_shift( $numbers ),
				);
			} else if ( 'o' == $expression[ $i ] ) {
				$group[] = array(
					'type' => 'operator',
					'value' => array_shift( $operators ),
				);
			} else if ( '(' == $expression[ $i ] ) {
				if ( isset( $elements ) ) {
					unset( $elements );
				}
				$elements = array();
				$subgroup = array(
					'type' => 'group',
					'nodes' => &$elements,
				);
				$group[]  = $subgroup;
				$stack[]  = &$group;
				unset( $group );
				$group    = &$elements;
			} else if ( ')' == $expression[ $i ] ) {
				$top = &$stack[ count( $stack ) - 1 ];
				array_pop( $stack );
				$group = &$top;
			}
		}

		$nodes = array(
			'type' => 'group',
			'nodes' => $nodes,
		);

		$this->canonicalize( $nodes );
		$this->apply_precedence( $nodes );
		$this->canonicalize( $nodes );

		$this->ast = $nodes;
	}

	/**
	 * Get parsed expression
	 *
	 * @return string
	 */
	public function get_parsed_expression() {
		 return $this->to_string( $this->ast );
	}
	/**
	 * Evaluate
	 *
	 * @param array $arguments Args.
	 * @return type
	 */
	public function evaluate( $arguments = array() ) {
		return $this->reduce( $this->ast, $arguments );
	}
	/**
	 * Compute
	 *
	 * @param string $operator Operator.
	 * @param array  $arguments Args.
	 * @return type
	 * @throws Exception Unknown operator.
	 */
	private function wt_compute( $operator, $arguments ) {
		if ( '!' == $operator ) {
			return (int) ( ! $arguments[0] );
		} else if ( '/' == $operator ) {
			return $arguments[0] / $arguments[1];
		} else if ( '*' == $operator ) {
			return $arguments[0] * $arguments[1];
		} else if ( '-' == $operator ) {
			return $arguments[0] - $arguments[1];
		} else if ( '+' == $operator ) {
			return $arguments[0] + $arguments[1];
		} else if ( '&&' == $operator ) {
			return (int) ( $arguments[0] && $arguments[1] );
		} else if ( '||' == $operator ) {
			return (int) ( $arguments[0] || $arguments[1] );
		} else if ( '<' == $operator ) {
			return (int) ( $arguments[0] < $arguments[1] );
		} else if ( '>' == $operator ) {
			return (int) ( $arguments[0] > $arguments[1] );
		} else if ( '<=' == $operator ) {
			return (int) ( $arguments[0] <= $arguments[1] );
		} else if ( '>=' == $operator ) {
			return (int) ( $arguments[0] >= $arguments[1] );
		} else if ( '!=' == $operator ) {
			return (int) ( $arguments[0] != $arguments[1] );
		} else if ( '==' == $operator ) {
			return (int) ( $arguments[0] == $arguments[1] );
		} else {
			throw new Exception( sprintf( 'Unknown operator %s !', esc_html( $operator ) ) );
		}
	}
	/**
	 * Reduce
	 *
	 * @param array $node Nodes.
	 * @param array $arguments Args.
	 * @return type
	 * @throws Exception Unknown.
	 */
	private function reduce( $node, $arguments ) {
		if ( 'application' == $node['type'] ) {
			$ops = array();
			foreach ( $node['operands'] as $operand ) {
				$ops[] = $this->reduce( $operand, $arguments );
			}
			return $this->wt_compute( $node['operator'], $ops );
		} else if ( 'number' == $node['type'] ) {
			return $node['value'];
		} else if ( 'variable' == $node['type'] ) {
			if ( isset( $arguments[ $node['value'] ] ) ) {
				return $arguments[ $node['value'] ];
			} else {
				throw new Exception( sprintf( 'Variable %s was not assigned!', esc_html( $node['value'] ) ) );
			}
		} else {
			throw new Exception( "Don't know how to reduce node with type " . esc_html( $node['type'] ) );
		}
	}

	/**
	 * To string
	 *
	 * @param array $node Nodes.
	 * @return type
	 */
	private function to_string( $node ) {
		if ( 'group' == $node['type'] ) {
			return '[ ' . implode( ' ', array_map( array( $this, 'to_string' ), $node['nodes'] ) ) . ' ]';
		} else if ( 'application' == $node['type'] ) {
			if ( 1 == $this->operators[ $node['operator'] ] ) {
				return '( ' . $node['operator'] . $this->to_string( $node['operands'][0] ) . ' )';
			} else {
				return '( ' . $this->to_string( $node['operands'][0] ) . ' ' . $node['operator'] . ' ' . $this->to_string( $node['operands'][1] ) . ' )';
			}
		} else {
			return $node['value'];
		}
	}

	/**
	 *  Remove superfluous parentheses
	 *
	 * @param array $node Nodes.
	 */
	private function canonicalize( &$node ) {
		if ( 'group' == $node['type'] ) {
			foreach ( $node['nodes'] as &$child ) {
				$this->canonicalize( $child );
			}
			if ( 1 == count( $node['nodes'] ) ) {
				$node = $node['nodes'][0];
			}
		} else if ( 'application' == $node['type'] ) {
			foreach ( $node['operands'] as &$child ) {
				$this->canonicalize( $child );
			}
		}
	}
	/**
	 * Apply precedence
	 *
	 * @param array $node Nodes.
	 */
	private function apply_precedence( &$node ) {
		if ( 'group' == $node['type'] ) {
			foreach ( $node['nodes'] as &$child ) {
				$this->apply_precedence( $child );
			}
			foreach ( $this->operators as $operator => $arity ) {
				do {
					$index = -1;
					$node_count = count( $node['nodes'] );
					for ( $i = 0; $i < $node_count; ++$i ) {
						if ( ( 'operator' == $node['nodes'][ $i ]['type'] ) && ( $node['nodes'][ $i ]['value'] == $operator ) ) {
							$index = $i;
							break;
						}
					}
					if ( $index >= 0 ) {
						$new_nodes = ( 1 == $arity ) ? array_slice( $node['nodes'], 0, $index ) : array_slice( $node['nodes'], 0, $index - 1 );
						$operands  = ( 1 == $arity ) ? array( $node['nodes'][ $index + 1 ] ) : array( $node['nodes'][ $index - 1 ], $node['nodes'][ $index + 1 ] );
						$application = array(
							'type' => 'application',
							'operator' => $operator,
							'operands' => $operands,
						);
						$new_nodes[] = $application;
						$new_nodes = array_merge( $new_nodes, array_slice( $node['nodes'], $index + 2 ) );
						$node['nodes'] = $new_nodes;
					}
				} while ( $index >= 0 );
			}
		}
	}
}
