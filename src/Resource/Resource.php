<?php

namespace Tobyz\JsonApiServer\Resource;

use Tobyz\JsonApiServer\Context;
use Tobyz\JsonApiServer\Schema\Field\Field;

abstract class Resource implements ResourceInterface
{
    public function endpoints(): array
    {
        return [];
    }

    public function fields(): array
    {
        return [];
    }

    public function meta(): array
    {
        return [];
    }

    public function filters(): array
    {
        return [];
    }

    public function sorts(): array
    {
        return [];
    }

    public function getId(object $model, Context $context): string
    {
        return $model->id;
    }

    public function getValue(object $model, Field $field, Context $context): mixed
    {
        return $model->{$field->property ?: $field->name} ?? null;
    }
}
