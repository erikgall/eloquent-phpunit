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
     * @param array $expected
     * @param array $actual
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
     * Assert the model casts the attributes to a carbon instance.
     *
     * @param  array|string $dates
     * @param  string|bool|null $timestamps
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

    /**
     * Assert that a model has the hidden fields.
     *
     * @return $this
     */
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
        return $this->dataKey('casts');
    }

    /**
     * Get the model's dates array.
     *
     * @return array
     */
    protected function dates()
    {
        return $this->dataKey('dates');
    }

    /**
     * Get a data key by name.
     *
     * @param string $key
     * @return array|string
     */
    protected function dataKey($key)
    {
        if ($this->keyNeedsSet($key)) {
            $method = 'get'.ucfirst($key);

            $this->data[$key] = $this->subject->$method();
        }

        return $this->data[$key];
    }

    /**
     * Get the model's fillable attributes array.
     *
     * @return array
     */
    protected function fillable()
    {
        return $this->dataKey('fillable');
    }

    /**
     * Get the model's hidden attributes array.
     *
     * @return array
     */
    protected function hidden()
    {
        return $this->dataKey('hidden');
    }

    /**
     * Check if a key in the data array is null.
     *
     * @param string $key
     * @return bool
     */
    protected function keyNeedsSet($key)
    {
        return is_null($this->data[$key]);
    }
}
