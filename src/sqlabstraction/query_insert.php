<?php
/**
 * File containing the ezcQueryInsert class.
 *
 * @package Database
 * @version 1.4.7
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Class to create select database independent INSERT queries.
 *
 * Note that this class creates queries that are syntactically independant
 * of database. Semantically the queries still differ and so the same
 * query may produce different results on different databases. Such
 * differences are noted throughout the documentation of this class.
 *
 * This class implements SQL92. If your database differs from the SQL92
 * implementation extend this class and reimplement the methods that produce
 * different results. Some methods implemented in ezcQuery are not defined by SQL92.
 * These methods are marked and ezcQuery will return MySQL syntax for these cases.
 *
 * The examples show the SQL generated by this class.
 * Database specific implementations may produce different results.
 *
 * Example:
 * <code>
 * $q = ezcDbInstance::get()->createInsertQuery();
 * $q->insertInto( 'legends' )
 *        ->set( 'name', $q->bindValue( 'Gretzky' ) )
 *        ->set( 'year', $q->bindValue( 1961 ) );
 * $stmt = $q->prepare();
 * $stmt->execute();
 * </code>
 *
 * @package Database
 * @version 1.4.7
 * @mainclass
 */
class ezcQueryInsert extends ezcQuery
{
    /**
     * Holds the columns and the values that should inserted into the the table.
     *
     * Format array('column'=>value)
     * @var array(string=>mixed)
     */
    protected $values = array();

    /**
     * The target table for the insert query.
     *
     * @var string
     */
    private $table = null;

    /**
     * Constructs a new ezcQueryInsert that works on the database $db and with the aliases $aliases.
     *
     * The parameters are passed directly to ezcQuery.
     * @param PDO $db
     * @param array(string=>string) $aliases
     */
    public function __construct( $db, array $aliases = array() )
    {
        parent::__construct( $db, $aliases );
    }

    /**
     * Opens the query and sets the target table to $table.
     *
     * insertInto() returns a pointer to $this.
     *
     * @param string $table
     * @return ezcQueryInsert
     */
    public function insertInto( $table )
    {
        $table = $this->getIdentifier( $table );
        $this->table = $table;
        return $this;
    }

    /**
     * The insert query will set the column $column to the value $expression.
     *
     * set() returns a pointer to $this.
     *
     * @param string $column
     * @param string $expression
     * @return ezcQueryInsert
     */
    public function set( $column, $expression )
    {
        $column = $this->getIdentifier( $column );
        $expression = $this->getIdentifier( $expression );

        if ( $this->db->getName() == 'oracle' )
        {
            // This is "quick fix" for the case of setting sequence value in Oracle.
            // Assume that set( 'columnName', "nextval('sequenceName')") was called.
            // Converting sequence SQL "nextval('sequenceName')" that valid for PostgreSQL
            // to "sequenceName.nextval" that valid for Oracle.

            if ( preg_match( "/nextval\('(.*)'\)/", $expression, $matches ) )
            {
                $sequenceName = $matches[1];
                $expression = $sequenceName.'.nextval';
                $this->values[$column] = $expression;

                return $this;
            }
        }

        $this->values[$column] = $expression;
        return $this;
    }

    /**
     * Returns the query string for this query object.
     *
     * @throws ezcQueryInvalidException if no table or no values have been set.
     * @return string
     */
    public function getQuery()
    {
        if ( $this->table == null || empty( $this->values ) )
        {
            $problem = $this->table == null ? 'table' : 'values';
            throw new ezcQueryInvalidException( "INSERT", "No " . $problem . " set." );
        }
        $query = "INSERT INTO {$this->table}";
        $columns = implode( ', ', array_keys( $this->values ) );
        $values = implode( ', ', array_values( $this->values ) );
        $query .= " ( {$columns} ) VALUES ( {$values} )";
        return $query;
    }
}
