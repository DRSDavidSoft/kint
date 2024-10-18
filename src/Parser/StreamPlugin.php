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

namespace Kint\Parser;

use Kint\Zval\AbstractValue;
use Kint\Zval\Context\ArrayContext;
use Kint\Zval\Representation\Representation;
use Kint\Zval\ResourceValue;
use Kint\Zval\StreamValue;
use TypeError;

class StreamPlugin extends AbstractPlugin implements PluginCompleteInterface
{
    public function getTypes(): array
    {
        return ['resource'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        if (!$v instanceof ResourceValue) {
            return $v;
        }

        // Doublecheck that the resource is open before we get the metadata
        if (!\is_resource($var)) {
            return $v;
        }

        try {
            $meta = \stream_get_meta_data($var);
        } catch (TypeError $e) {
            return $v;
        }

        $c = $v->getContext();
        $stream = new StreamValue($c, $meta);
        $stream->appendHints($v->getHints());
        $stream->appendRepresentations($v->getRepresentations());

        $rep = new Representation('Stream');
        $rep->implicit_label = true;
        $rep->contents = [];

        $parser = $this->getParser();
        foreach ($meta as $key => $val) {
            $base = new ArrayContext($key);
            $base->depth = $c->getDepth() + 1;

            if (null !== ($ap = $c->getAccessPath())) {
                $base->access_path = 'stream_get_meta_data('.$ap.')['.\var_export($key, true).']';
            }

            $rep->contents[] = $parser->parse($val, $base);
        }

        $stream->addRepresentation($rep, 0);

        return $stream;
    }
}
