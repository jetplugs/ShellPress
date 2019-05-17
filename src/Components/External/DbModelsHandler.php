<?php
namespace shellpress\v1_3_71\src\Components\External;

/**
 * @author jakubkuranda@gmail.com
 * Date: 04.02.2019
 * Time: 15:31
 */

use shellpress\v1_3_71\src\Shared\Components\IComponent;
use wpdb;

class DbModelsHandler extends IComponent {

	/** @var string[] */
	private $_modelNames = array();

	/**
	 * Called on creation of component.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		//  ----------------------------------------
		//  ACTIONS
		//  ----------------------------------------

		$this::s()->event->addOnActivate( array( $this, '_a_createTables' ) );

	}

	/**
	 * Adds model for later registeration process.
	 *
	 * @param string $name
	 *
	 * @return string Sanitized name.
	 */
	public function registerModel( $name ) {

		$name = $this->_sanitizeSafe( $name );

		$this->_modelNames[] = $name;

		return $name;

	}

	/**
	 * Removes all odd symbols and converts dashes to underscores.
	 *
	 * @param $name
	 *
	 * @return mixed|string
	 */
	private function _sanitizeSafe( $name ) {

		$sanitizedName = sanitize_key( $name );
		$sanitizedName = str_replace( array( '-' ), '_', $sanitizedName );

		return $sanitizedName;

	}

	/**
	 * Returns database base table name for given model name.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private function _getModelTableName( $name ) {

		global $wpdb;   /** @var wpdb $wpdb */

		return $wpdb->prefix . $this->_sanitizeSafe( $name );

	}

	/**
	 * Returns database meta table name for given model name.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private function _getMetaTableName( $name ) {

		global $wpdb;   /** @var wpdb $wpdb */

		return $wpdb->prefix . $this->_sanitizeSafe( $name ) . '_meta';

	}

	/**
	 * @param string $name
	 */
	private function _createDbTableModel( $name ) {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$tableName      = $this->_getModelTableName( $name );
		$charsetCollate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
  			created datetime NULL,
			PRIMARY KEY  (id)
			) {$charsetCollate};";

		dbDelta( $sql );

	}

	/**
	 * @param string $name
	 */
	private function _createDbTableMeta( $name ) {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$tableName      = $this->_getMetaTableName( $name );
		$charsetCollate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			model_id bigint(20) NOT NULL,
			meta_key varchar(100) NOT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY meta_id (meta_id),
			KEY model_id (model_id),
			KEY meta_key (meta_key),
			KEY meta_value (meta_value(24))
			) {$charsetCollate};";

		dbDelta( $sql );

	}

	/**
	 * Creates sql part for multidimensional metaquery.
	 *
	 *
	 * @param string $modelName
	 * @param array $conditions
	 * @param int $metaTableAliasIndex
	 *
	 * @return string
	 */
	private function _getSqlPartForMetaQuery( $modelName, $conditions, &$metaTableAliasIndex = 1 ) {

		global $wpdb;   /** @var wpdb $wpdb */

		$sqlParts = array();

		$relation = isset( $conditions['relation'] ) ? strtoupper( $conditions['relation'] ) : 'AND';

		//  ----------------------------------------
		//  Let's look at conditions
		//  ----------------------------------------

		foreach( $conditions as $condition ){

			$tableName = "m" . $metaTableAliasIndex;

			if( is_array( $condition ) ){

				//  Condition or nested group?

				if( isset( $condition['key'] ) ){

					//  ----------------------------------------
					//  Defaults
					//  ----------------------------------------

					$defCondition = array(
						'key'       =>  '',
						'value'     =>  '',
						'compare'   =>  '='
					);

					$condition = wp_parse_args( $condition, $defCondition );

					//  ----------------------------------------
					//  Generate compare
					//  ----------------------------------------

					switch( $condition['compare'] ){

						case '=':
						case '>=':
						case '<=':
						case '!=':
						case '>':
						case '<':

							$sqlParts[] = $wpdb->prepare( "( {$tableName}.meta_key = %s AND {$tableName}.meta_value {$condition['compare']} %s )", array( $condition['key'], $condition['value'] ) );

							break;

						case 'BETWEEN':
						case 'between':

							//  Value should be an array containing two elements.
							if( is_array( $condition['value'] )
							    && isset( $condition['value'][0] )
							    && isset( $condition['value'][1] ) ){

								$sqlParts[] = $wpdb->prepare( "( {$tableName}.meta_key = %s AND ( {$tableName}.meta_value BETWEEN %s AND %s ) )", array( $condition['key'], $condition['value'][0], $condition['value'][1] ) );

							}

							break;

					}

				} else {

					//  This is nested group. Run recursive method.
					$sqlParts[] = $this->_getSqlPartForMetaQuery( $modelName, $condition, $metaTableAliasIndex );

				}

				$metaTableAliasIndex++; //  <-- Important! Make index bigger.

			}

		}

		return sprintf( '( %1$s )', implode( " {$relation} ", $sqlParts ) );

	}

	/**
	 * @param string $modelName
	 * @param int $modelId
	 * @param string $metaKey
	 *
	 * @return mixed|bool
	 */
	private function _getCacheMeta( $modelName, $modelId, $metaKey ) {

		$modelMetaCache = wp_cache_get( $modelId, "{$modelName}_meta" );

		if( is_array( $modelMetaCache ) && isset( $modelMetaCache[$metaKey] ) ){
			return $modelMetaCache[$metaKey];
		}

		return false;

	}

	/**
	 * @param string $modelName
	 * @param int $modelId
	 * @param string $metaKey
	 * @param mixed $value
	 *
	 * @return bool
	 */
	private function _setCacheMeta( $modelName, $modelId, $metaKey, $value ) {

		$modelMetaCache = wp_cache_get( $modelId, "{$modelName}_meta" );

		if( ! is_array( $modelMetaCache ) ){
			$modelMetaCache = array();
		}

		$modelMetaCache[$metaKey] = $value;

		return wp_cache_set( $modelId, $modelMetaCache, "{$modelName}_meta" );

	}

	/**
	 * @param string $modelName
	 * @param int $modelId
	 * @param string|null $metaKey
	 *
	 * @return bool
	 */
	private function _deleteCacheMeta( $modelName, $modelId, $metaKey = null ) {

		if( $metaKey ){

			$modelMetaCache = wp_cache_get( $modelId, "{$modelName}_meta" );

			if( is_array( $modelMetaCache ) && isset( $modelMetaCache[$metaKey] ) ){
				unset( $modelMetaCache[$metaKey] );
				return wp_cache_set( $modelId, $modelMetaCache, "{$modelName}_meta" );
			}

			return false;

		} else {

			return wp_cache_delete( $modelId, "{$modelName}_meta" );

		}

	}

	/**
	 * This method may be used to generate new model or update existing one.
	 *
	 * @param string $modelName
	 *
	 * @return int
	 */
	public function insertModel( $modelName ) {

		global $wpdb; /** @var wpdb $wpdb */

		//  ----------------------------------------
		//  Do DB query
		//  ----------------------------------------

		$result = $wpdb->insert( $this->_getModelTableName( $modelName ), array( 'created' => current_time( 'mysql', true ) ) );

		return $result ? $wpdb->insert_id : 0;

	}

	/**
	 * @param string $modelName
	 * @param int $modelId
	 *
	 * @return bool
	 */
	public function deleteModel( $modelName, $modelId ) {

		global $wpdb; /** @var wpdb $wpdb */

		//  ----------------------------------------
		//  Do DB query
		//  ----------------------------------------

		$result = $wpdb->delete( $this->_getModelTableName( $modelName ), array( 'id' => $modelId ) );

		//  ----------------------------------------
		//  Delete meta
		//  ----------------------------------------

		if( $result ){

			$this->deleteMeta( $modelName, $modelId );
			$this->pushChanges( $modelName, $modelId );

		}

		return (bool) $result;

	}

	/**
	 * Finds models in database.
	 * Example of metaQuery:
	 *
	 * array(
	 *      'relation'  =>  'AND',
	 *      array(
	 *          'key'       =>  'key1',
	 *          'value'     =>  '12',
	 *          'compare'   =>  '>='
	 *      ),
	 *      array(
	 *          'key'       =>  'key2',
	 *          'value'     =>  '14',
	 *          'compare'   =>  '<='
	 *      ),
	 *      array(
	 *          'relation'  =>  'AND',
	 *          array(
	 *              'key'       =>  'key3',
	 *              'value'     =>  '16',
	 *              'compare'   =>  '='
	 *          )
	 *      )
	 * )
	 *
	 * @param string $modelName
	 * @param array $options
	 * @param array $metaQuery
	 *
	 * @return int[]
	 */
	public function findModels( $modelName, $options = array(), $metaQuery = array() ) {

		global $wpdb;   /** @var wpdb $wpdb */

		$modelTableName = $this->_getModelTableName( $modelName );
		$metaTableName  = $this->_getMetaTableName( $modelName );

		//  ----------------------------------------
		//  Options
		//  ----------------------------------------

		$defOptions = array(
			'page'      =>  1,
			'perPage'   =>  10,
			'return'    =>  'ids'  //  ids, count
		);

		$options = wp_parse_args( $options, $defOptions );

		//  ----------------------------------------
		//  Base of SQL
		//  ----------------------------------------

		switch( $options['return'] ){
			case 'ids':
				$selectWhat = 'id';
				break;

			case 'count':
				$selectWhat = 'count(*)';
				break;

			default:
				$selectWhat = '*';
		}

		$sql = "SELECT {$selectWhat} FROM {$modelTableName}" . PHP_EOL;

		//  ----------------------------------------
		//  Meta query
		//  ----------------------------------------

		if( ! empty( $metaQuery ) ){

			/**
			 * We are looking for all ( maybe duplicated ) meta keys.
			 * We will use it for counting number of left joins.
			 */
			$metaKeys       = $this::s()->utility->listPluck( $metaQuery, 'key' );
			$numLeftJoins   = count( $metaKeys );

			/**
			 * Every condidtion made with meta key needs unique join on meta table.
			 */
			for( $leftJoinIndex = 1; $leftJoinIndex <= $numLeftJoins; $leftJoinIndex++ ){
				$sql .= " LEFT JOIN {$metaTableName} m{$leftJoinIndex} ON ( {$modelTableName}.id = m{$leftJoinIndex}.model_id )" . PHP_EOL;
			}

			$sql .= " WHERE " . $this->_getSqlPartForMetaQuery( $modelName, $metaQuery ) . PHP_EOL;

		}

		//  ----------------------------------------
		//  GROUP BY
		//  ----------------------------------------

		$sql .= " GROUP BY {$modelTableName}.id" . PHP_EOL;

		//  ----------------------------------------
		//  ORDER BY
		//  ----------------------------------------

		$sql .= " ORDER BY {$modelTableName}.id" . PHP_EOL;

		//  ----------------------------------------
		//  Pagination
		//  ----------------------------------------

		if( (int) $options['perPage'] > 0 ){    //  If perPage is set to 0, it will not set any limits.

			$limit = (int) $options['perPage'];
			$offset = ( intval( $options['page'] ) - 1 ) * $limit;

			$sql .= " LIMIT {$limit} OFFSET {$offset}";

		}

		//  ----------------------------------------
		//  Return
		//  ----------------------------------------

		$results = $wpdb->get_results( $sql, 'ARRAY_A' );
		$ids = array();

		if( is_array( $results ) ){

			//  Returns ids

			if( $options['return'] === 'ids' ){

				foreach( $results as $row ){
					$ids[] = $row['id'];
				}

			}

		}

		return $ids;

	}

	/**
	 * @param string $modelName
	 * @param int    $modelId
	 * @param string $metaKey
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getMeta( $modelName, $modelId, $metaKey, $defaultValue = '' ) {

		global $wpdb; /** @var wpdb $wpdb */

		$tableName = $this->_getMetaTableName( $modelName );

		//  ----------------------------------------
		//  Do DB query
		//  ----------------------------------------

		$cached = $this->_getCacheMeta( $modelName, $modelId, $metaKey );

		if( $cached ){

			return $cached;

		} else {

			$result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$tableName} WHERE model_id = %s AND meta_key = %s", array( $modelId, $metaKey ) ) );
			$result = $result ? maybe_unserialize( $result ) : $defaultValue;

			$this->_setCacheMeta( $modelName, $modelId, $metaKey, $result );

			return $result ?: $defaultValue;

		}

	}

	/**
	 * @param string $modelName
	 * @param int $modelId
	 * @param string $metaKey
	 * @param mixed $value
	 *
	 * @return int Meta ID or 0 if not inserted.
	 */
	public function insertMeta( $modelName, $modelId, $metaKey, $value ) {

		global $wpdb; /** @var wpdb $wpdb */

		$tableName = $this->_getMetaTableName( $modelName );

		$valueSerialized = maybe_serialize( $value );

		//  ----------------------------------------
		//  Prepare data
		//  ----------------------------------------

		$data = array(
			'model_id'      =>  (int) $modelId,
			'meta_key'      =>  $metaKey,
			'meta_value'    =>  $valueSerialized,
		);

		//  ----------------------------------------
		//  Check if its update or insert
		//  ----------------------------------------

		$getMetaIdResult = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM {$tableName} WHERE model_id = %s AND meta_key = %s", array( $modelId, $metaKey ) ) );

		if( $getMetaIdResult ) $data['meta_id'] = $getMetaIdResult;

		//  ----------------------------------------
		//  Do DB query
		//  ----------------------------------------

		$result = $wpdb->replace( $tableName, $data );

		if( $result ){

			$this->_setCacheMeta( $modelName, $modelId, $metaKey, $value );

			return $wpdb->insert_id;

		} else {

			return 0;

		}

	}

	/**
	 * @param string $modelName
	 * @param int $modelId
	 * @param string|null $metaKey
	 *
	 * @return bool
	 */
	public function deleteMeta( $modelName, $modelId, $metaKey = null ) {

		global $wpdb; /** @var wpdb $wpdb */

		//  ----------------------------------------
		//  Do DB query
		//  ----------------------------------------

		if( $metaKey ){

			$result = $wpdb->delete( $this->_getMetaTableName( $modelName ), array( 'meta_key' => $metaKey, 'model_id' => $modelId ) );

		} else {

			$result = $wpdb->delete( $this->_getMetaTableName( $modelName ), array( 'model_id' => $modelId ) );

		}

		if( $result ){

			$this->_deleteCacheMeta( $modelName, $modelId, $metaKey );

			return true;

		} else {

			return false;

		}

	}

	/**
	 * This method applies meta changes in database.
	 * Use it every time after inserting or deleting meta from model.
	 * - current state: not doing anything. Please use it for further compatibility.
	 *
	 * @param string $modelName
	 * @param int $modelId
	 *
	 * @return bool
	 */
	public function pushChanges( $modelName, $modelId ) {

		//  TODO - implementation.

		return true;

	}

	//  ================================================================================
	//  ACTIONS
	//  ================================================================================

	/**
	 * Called on plugin activation hook.
	 *
	 * @return void
	 */
	public function _a_createTables() {

		foreach( $this->_modelNames as $modelName ){

			$this->_createDbTableModel( $modelName );
			$this->_createDbTableMeta( $modelName );

		}

	}

}