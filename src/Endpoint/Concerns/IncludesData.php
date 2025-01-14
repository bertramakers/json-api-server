<?php

namespace Tobyz\JsonApiServer\Endpoint\Concerns;

use Tobyz\JsonApiServer\Context;
use Tobyz\JsonApiServer\Exception\BadRequestException;
use Tobyz\JsonApiServer\Schema\Field\Relationship;

trait IncludesData
{
    private function getInclude(Context $context): array
    {
        if ($includeString = $context->request->getQueryParams()['include'] ?? null) {
            $include = $this->parseInclude($includeString);

            $this->validateInclude($context, [$context->resource], $include);

            return $include;
        }

        return [];
    }

    private function parseInclude($include): array
    {
        $tree = [];

        foreach (is_array($include) ? $include : explode(',', $include) as $path) {
            $array = &$tree;

            foreach (explode('.', $path) as $key) {
                if (!isset($array[$key])) {
                    $array[$key] = [];
                }

                $array = &$array[$key];
            }
        }

        return $tree;
    }

    private function validateInclude(
        Context $context,
        array $resources,
        array $include,
        string $path = '',
    ): void {
        foreach ($include as $name => $nested) {
            foreach ($resources as $resource) {
                $fields = $context->fields($resource);

                if (
                    !($field = $fields[$name] ?? null) ||
                    !$field instanceof Relationship ||
                    !$field->includable
                ) {
                    continue;
                }

                $types = $field->types;

                $relatedResources = $types
                    ? array_map(fn($type) => $context->api->getResource($type), $types)
                    : array_values($context->api->resources);

                $this->validateInclude($context, $relatedResources, $nested, $name . '.');

                continue 2;
            }

            throw new BadRequestException("Invalid include [{$path}{$name}]", [
                'parameter' => 'include',
            ]);
        }
    }
}
