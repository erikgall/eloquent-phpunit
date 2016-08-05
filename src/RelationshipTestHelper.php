<?php

namespace EGALL\EloquentPHPUnit;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Relationship test helper trait.
 * 
 * @author Erik Galloway <erik@mybarnapp.com>
 */
trait RelationshipTestHelper
{
    /**
     * Assert the subject has a relationship with another model.
     * 
     * @param  string $relationship
     * @param  string $model
     * @param  string $method
     * @return $this
     */
    public function assertHasRelationship($relationship, $model, $method)
    {
        $this->assertInstanceOf($relationship, $this->subject->{$method}());
        $this->assertInstanceOf($model, $this->subject->{$method}()->getModel());

        return $this;
    }

    /**
     * Assert the model has a belongs to relationship.
     * 
     * @param  string $model
     * @param  string|null $name
     * @return $this
     */
    public function belongsTo($model, $name = null)
    {
        $method = $name ?: $this->getRelationshipMethodName($model);

        return $this->assertHasRelationship(BelongsTo::class, $model, $method);
    }

    /**
     * Assert the model has a has many relationship.
     * 
     * @param  string $model
     * @param  string|null $name
     * @return bool
     */
    public function hasMany($model, $name = null)
    {
        return $this->assertHasRelationship(
            HasMany::class, $model, $name ?: $this->getRelationshipMethodName($model, false)
        );
    }

    /**
     * Assert the model has a morphs to relationship.
     * 
     * @param  string $method
     * @param  string|null
     * @return $this
     */
    public function morphsTo($method, $morphTo = null)
    {
        $this->assertInstanceOf(MorphTo::class, $this->subject->$method());
        $this->assertEquals($this->subject->$method()->getMorphType(), ($morphTo ?: $method).'_type');

        return $this;
    }

    /**
     * Get a relationship's method name.
     * 
     * @param  string $model
     * @param  bool $singular
     * @return string
     */
    protected function getRelationshipMethodName($model, $singular = true)
    {
        $name = camel_case(class_basename($model));

        return $singular ? $name : str_plural($name);
    }
}
