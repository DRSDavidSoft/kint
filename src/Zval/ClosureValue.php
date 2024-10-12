<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Jonathan Vollebregt (jnvsor@gmail.com), Rokas Šleinius (raveren@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kint\Zval;

use Closure;
use Kint\Zval\Context\BaseContext;
use Kint\Zval\Context\ContextInterface;
use ReflectionFunction;

class ClosureValue extends InstanceValue
{
    use ParameterHoldingTrait;

    /** @psalm-var array<string, true> */
    public array $hints = [
        'callable' => true,
        'closure' => true,
    ];

    /** @psalm-param class-string $classname */
    public function __construct(ContextInterface $context, Closure $cl)
    {
        parent::__construct($context, \get_class($cl), \spl_object_hash($cl), \spl_object_id($cl));

        /**
         * @psalm-var ContextInterface $this->context
         * Psalm bug #11113
         */
        $closure = new ReflectionFunction($cl);

        if ($closure->isUserDefined()) {
            $this->filename = $closure->getFileName();
            $this->startline = $closure->getStartLine();
        }

        foreach ($closure->getParameters() as $param) {
            $this->parameters[] = new ParameterBag($param);
        }

        if (!$this->context instanceof BaseContext) {
            return;
        }

        if (0 !== $this->context->getDepth()) {
            return;
        }

        $ap = $this->context->getAccessPath();

        if (null === $ap) {
            return;
        }

        if (\preg_match('/^\\((function|fn)\\s*\\(/i', $ap, $match)) {
            $this->context->name = \strtolower($match[1]);
        }
    }

    public function getSize(): ?string
    {
        return null;
    }

    public function transplant(Value $old): void
    {
        $stash = [$this->filename, $this->startline];
        parent::transplant($old);
        [$this->filename, $this->startline] = $stash;
    }

    public function getDisplayName(): string
    {
        return $this->context->getName().'('.$this->getParams().')';
    }
}
