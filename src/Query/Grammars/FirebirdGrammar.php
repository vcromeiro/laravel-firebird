<?php

namespace Firebird\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;

class FirebirdGrammar extends Grammar
{
    /**
     * All of the available clause operators.
     *
     * @var array
     */
    protected $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'like', 'not like', 'between', 'containing', 'starting with',
        'similar to', 'not similar to',
    ];

    /**
     * Compile an aggregated select clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $aggregate
     * @return string
     */
    protected function compileAggregate(Builder $query, $aggregate)
    {
        $column = $this->columnize($aggregate['columns']);

        // If the query has a "distinct" constraint and we're not asking for all columns
        // we need to prepend "distinct" onto the column name so that the query takes
        // it into account when it performs the aggregating operations on the data.
        if ($query->distinct && $column !== '*') {
            $column = 'distinct '.$column;
        }

        return 'select '.$aggregate['function'].'('.$column.') as "aggregate"';
    }

    /**
     * Compile SQL statement for get context variable value.
     *
     * @param \Illuminate\Database\Query\Builder  $query
     * @param string $namespace
     * @param string $name
     * @return string
     */
    public function compileGetContext(Builder $query, $namespace, $name)
    {
        return "SELECT RDB\$GET_CONTEXT('{$namespace}', '{$name}' AS VAL FROM RDB\$DATABASE";
    }

    /**
     * Compile SQL statement for a stored procedure.
     *
     * @param \Illuminate\Database\Query\Builder  $query
     * @param string $procedure
     * @param array $values
     * @return string
     */
    public function compileProcedure(Builder $query, $procedure, array $values = null)
    {
        $procedure = $this->wrap($procedure);

        return $procedure.' ('.$this->parameterize($values).')';
    }

    /**
     * Compile the "limit" portions of the query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $limit
     * @return string
     */
    protected function compileLimit(Builder $query, $limit)
    {
        if ($query->offset) {
            $first = (int) $query->offset + 1;

            return 'ROWS '.(int) $first;
        } else {
            return 'ROWS '.(int) $limit;
        }
    }

    /**
     * Compile the "offset" portions of the query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $offset
     * @return string
     */
    protected function compileOffset(Builder $query, $offset)
    {
        if ($query->limit) {
            if ($offset) {
                $end = (int) $query->limit + (int) $offset;

                return 'TO '.$end;
            } else {
                return '';
            }
        } else {
            $begin = (int) $offset + 1;

            return 'ROWS '.$begin.' TO 2147483647';
        }
    }

    /**
     * Compile the random statement into SQL.
     *
     * @param  string  $seed
     * @return string
     */
    public function compileRandom($seed)
    {
        return 'RAND()';
    }

    /**
     * Compile a date based where clause.
     *
     * @param  string  $type
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function dateBasedWhere($type, Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return 'EXTRACT('.$type.' FROM '.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }

    /**
     * Compile a "where in" clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereIn(Builder $query, $where)
    {
        if (empty($where['values'])) {
            return '0 = 1';
        }

        // Work-around for the firebird where-in limit of 1500 values.
        if (count($where['values']) > 1500) {
            return $this->slicedWhereIn($query, $where, 1500);
        }

        return $this->wrap($where['column']).' in ('.$this->parameterize($where['values']).')';
    }

    /**
     * Compile a sliced where in query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $where
     * @param int $limit
     * @return string
     */
    private function slicedWhereIn(Builder $query, $where, $limit)
    {
        $sql = '';

        for ($i = 0; $i < ceil(count($where['values']) / $limit); $i++) {
            ($i !== 0) && $sql .= ' OR ';

            $sql .= static::whereIn(
                $query,
                $this->sliceWhereValues($where, $i * $limit, $limit)
            );
        }

        return '('.$sql.')';
    }

    /**
     * Slices the values portion of a $where array.
     *
     * @param array $where
     * @param int $offset
     * @param int $length
     * @return array
     */
    private function sliceWhereValues($where, $offset, $length)
    {
        $where['values'] = array_slice($where['values'], $offset, $length);

        return $where;
    }
}
