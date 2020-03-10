<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\DataSanitize;

interface RelationInterface
{
    public function merge(array $sources, string $target): void;
    public function delete(array $ids): void;
}
