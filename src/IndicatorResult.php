<?php

namespace DDI;

class IndicatorResult
{
    public function __construct(
        public readonly float $signal,
        public readonly string $detail,
    ) {}
}
