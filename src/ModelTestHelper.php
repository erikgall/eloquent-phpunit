<?php

namespace EGALL\EloquentPHPUnit;

/**
 * Eloquent model test helper trait.
 * 
 * @author Erik Galloway <erik@mybarnapp.com>
 */
trait ModelTestHelper
{
    /**
     * Assert that fields exist with in an array.
     * 
     * @param array  $expected
     * @param array  $actual
     * @param string $array
     * @return $this
     */
    public function hasAttributes(array $expected, array $actual, $array)
    {
        foreach ($expected as $field) {
            $this->assertTrue(
                in_array($field, $actual), "The {$field} attribute was not found in the {$array} array"
            );
        }

        return $this;
    }

    /**
     * Assert the model casts fields to their native type.
     * 
     * @param string|array
     * @param string|null
     * @return $this
     */
    public function hasCasts()
    {
        if (count($args = func_get_args()) == 2) {
            $casts = [$args[0] => $args[1]];
        } elseif (is_array($args[0])) {
            $casts = $args[0];
        }

        foreach ($casts as $field => $type) {
            $this->assertArrayHasKey($field, $this->casts, "The {$field} attribute is not located in the casts array.");
            $this->assertEquals($type, $this->casts[$field]);
        }

        return $this;
    }

    /**
     * Assert the model casts the attribitutes to a carbon instance.
     * 
     * @param  array $dates
     * @param  bool $timestamps
     * @return $this
     */
    public function hasDates($dates = [], $timestamps = true)
    {
        if (func_num_args() > 2) {
            $dates = func_get_args();
            $timestamps = true;
        }

        if (is_string($dates)) {
            $dates = (array) $dates;
        }

        $fields = $timestamps ? array_merge($dates, ['created_at', 'updated_at']) : $dates;

        return $this->hasAttributes($fields, $this->dates, 'dates');
    }

    /**
     * Assert the model has the fillable fields.
     * 
     * @param array $fields
     * @return $this
     */
    public function hasFillable()
    {
        $fields = func_get_args();

        if (count($fields) == 1) {
            $fields = $fields[0];
        }

        return $this->hasAttributes($fields, $this->fillable, 'fillable');
    }

    public function hasHidden()
    {
        $fields = func_get_args();
        if (count($fields) == 1) {
            $fields = is_array($fields[0]) ? $fields[0] : (array) $fields[0];
        }

        return $this->hasAttributes($fields, $this->hidden, 'hidden');
    }

    /**
     * Get the model's casts' array.
     * 
     * @return array
     */
    protected function casts()
    {
        if (is_null($this->data['casts'])) {
            $this->data['casts'] = $this->subject->getCasts();
        }

        return $this->data['casts'];
    }

    /**
     * Get the model's dates array.
     * 
     * @return array
     */
    protected function dates()
    {
        if (is_null($this->data['dates'])) {
            $this->data['dates'] = $this->subject->getDates();
        }

        return $this->data['dates'];
    }

    /**
     * Get the model's fillable attributes array.
     * 
     * @return array
     */
    protected function fillable()
    {
        if (is_null($this->data['fillable'])) {
            $this->data['fillable'] = $this->subject->getFillable();
        }

        return $this->data['fillable'];
    }

    /**
     * Get the model's hidden attributes array.
     * 
     * @return array
     */
    protected function hidden()
    {
        if (is_null($this->data['hidden'])) {
            $this->data['hidden'] = $this->subject->getHidden();
        }

        return $this->data['hidden'];
    }
}
