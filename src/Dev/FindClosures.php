<?php

namespace pxlrbt\FilamentExcel\Dev;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionObject;
use Throwable;

class FindClosures
{
    /** @var array<int> */
    private array $seen = [];

    /** @return array<string> */
    public function __invoke(mixed $target, string $path = 'root'): array
    {
        if ($target instanceof SerializableClosure) {
            return [];
        }

        if ($target instanceof Closure) {
            return [$path];
        }

        if (is_array($target)) {
            return $this->searchArray($target, $path);
        }

        if (! is_object($target)) {
            return [];
        }

        return $this->searchObject($target, $path);
    }

    /** @return array<string> */
    private function searchArray(array $target, string $path): array
    {
        $found = [];

        foreach ($target as $key => $value) {
            $found = array_merge($found, $this($value, "{$path}[{$key}]"));
        }

        return $found;
    }

    /** @return array<string> */
    private function searchObject(object $target, string $path): array
    {
        $objectId = spl_object_id($target);

        if (in_array($objectId, $this->seen, true)) {
            return [];
        }

        $this->seen[] = $objectId;

        $found = [];
        $ref = new ReflectionObject($target);

        while ($ref) {
            foreach ($ref->getProperties() as $property) {
                try {
                    $property->setAccessible(true);

                    if (! $property->isInitialized($target)) {
                        continue;
                    }

                    $value = $property->getValue($target);
                    $propPath = "{$path}->{$property->getName()}";
                    $found = array_merge($found, $this($value, $propPath));
                } catch (Throwable) {
                    // skip inaccessible properties
                }
            }

            $ref = $ref->getParentClass();
        }

        return $found;
    }
}
