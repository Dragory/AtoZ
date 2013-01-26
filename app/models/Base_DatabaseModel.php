<?php

abstract class Base_DatabaseModel
{
    protected // Rows to SELECT
              $get = ['*'],

              // Default sorting direction (asc/desc)
              $defaultSortDir = 'asc',

              // Valid columns to sort by (input value => real column)
              $sortColumns;

    /**
     * The base query object of this class.
     * @return object The query object for use in e.g. get() and single().
     */
    abstract protected function query();

    /**
     * Validates a given query order array (column, dir).
     * Requires $this->sortColumns to be set.
     * @param  array $order  The order array to validate
     * @return array         The validated order array
     * @throws Exception     If the prerequisities for validating are not met, throws an exception with an error message
     */
    public function validateOrder($order)
    {
        if (!$order) return null;

        // Make sure we have the prerequisities for validating order clauses
        if (!$this->sortColumns) throw new Exception('No sort column definitions!');
        if (count($order) != 2)  throw new Exception('Invalid query order!');

        // Case shouldn't matter here
        $inputColumn = strtolower($order[0]);
        $inputDir    = strtolower($order[1]);

        $outputColumn = '';
        $outputDir    = '';

        // Check the column
        // If we have the input column defined, use its value
        if (isset($this->sortColumns[$inputColumn]))
        {
            $outputColumn = $this->sortColumns[$inputColumn];
        }
        // Or if we have a column with a numeric key that matches the input column
        elseif (is_numeric(array_search($inputColumn, $this->sortColumns)))
        {
            $outputColumn = $inputColumn;
        }
        // Otherwise use the first found column
        else
        {
            $columns = array_values($this->sortColumns);
            $outputColumn = array_shift($columns);
            unset($columns);
        }

        // Check the direction
        if ($inputDir == 'asc' || $inputDir == 'desc')
            $outputDir = $inputDir;
        else
            $outputDir = $this->defaultSortDir;

        // Return the validated results
        return [$outputColumn, $outputDir];
    }

    /**
     * Fetch multiple records.
     * @param  array  $where  The WHERE clauses
     * @param  mixed  $order  An array containing ORDER, DIR or a string containing ORDER
     * @param  mixed  $limit  An array containing LIMIT, OFFSET or a string containing LIMIT
     * @return mixed          An array if records are found, null if not
     */
    protected function get(array $where = array(), $order = null, $limit = null, $get = null)
    {
        $query = $this->query();

        foreach ($where as $line)
        {
            if (is_array($line)) $query->where($line[0], $line[1], $line[2]);
            else $query->where($line);
        }

        if ($order)
        {
            if (!is_array($order)) $order = [$order, null];
            $order = $this->validateOrder($order);

            $query->order_by($order[0], $order[1]);
        }

        if ($limit)
        {
            if (is_array($limit)) $query->take($limit[0])->skip($limit[1]);
            else $query->take($limit);
        }

        // Allow changing the values we're getting
        if ($get)
            return $query->get($get);
        else
            return $query->get($this->get);
    }

    /**
     * Fetch a single record.
     * @param  array  $where  The WHERE clauses
     * @param  mixed  $order  An array containing ORDER, DIR or a string containing ORDER
     * @param  mixed  $limit  An array containing LIMIT, OFFSET or a string containing LIMIT
     * @param  array  $get    An array to override $this->get for this call
     * @return mixed          An object if a record is found, null if not
     */
    protected function single(array $where = array(), $order = null, $limit = null, $get = null)
    {
        $results = $this->get($where, $order, 1, $get);

        if (!$results) return null;
        return $results[0];
    }

    /**
     * Fetch multiple records and paginate them.
     * @param  array   $where    The WHERE clauses
     * @param  mixed   $order    An array containing ORDER, DIR or a string containing ORDER
     * @param  mixed   $limit    An array containing LIMIT, OFFSET or a string containing LIMIT
     * @param  integer $perPage  Amount of records shown per page
     * @return object            The paginator object of the records
     */
    protected function paginated(array $where = array(), $order = null, $perPage = 50)
    {
        $query = $this->query();

        foreach ($where as $line)
        {
            if (is_array($line)) $query->where($line[0], $line[1], $line[2]);
            else $query->where($line);
        }

        if ($order)
        {
            if (!is_array($order)) $order = [$order, null];
            $order = $this->validateOrder($order);

            $query->order_by($order[0], $order[1]);
        }

        return $query->paginate($perPage, $this->get);
    }

    /**
     * Fetch every record with the class's base query.
     * @return mixed  An array of result objects or null if none are found.
     */
    public function getAll()
    {
        return $this->get();
    }

    /**
     * @param  array $where  Gets the count of rows for set WHERE clauses
     * @return mixed
     */
    public function count($where = array())
    {
        $countQuery = $this->single($where, null, null, [DB::raw('COUNT(*) AS rowCount')]);
        return $countQuery->rowcount;
    }
}