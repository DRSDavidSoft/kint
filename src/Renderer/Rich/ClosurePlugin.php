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

namespace Kint\Renderer\Rich;

use Kint\Kint;
use Kint\Renderer\RichRenderer;
use Kint\Value\AbstractValue;
use Kint\Value\ClosureValue;
use Kint\Value\Context\ClassDeclaredContext;

class ClosurePlugin extends AbstractPlugin implements ValuePluginInterface
{
    public function renderValue(AbstractValue $o): ?string
    {
        if (!$o instanceof ClosureValue) {
            return null;
        }

        $c = $o->getContext();

        $children = $this->renderer->renderChildren($o);

        $header = '';

        if ($c instanceof ClassDeclaredContext) {
            $header .= '<var>'.$c->getModifiers().'</var> ';
        }

        $header .= '<dfn>'.$this->renderer->escape($o->getDisplayName()).'</dfn> ';

        $s = $o->getDisplayType();

        if (RichRenderer::$escape_types) {
            $s = $this->renderer->escape($s);
        }

        if ($c->isRef()) {
            $s = '&amp;'.$s;
        }

        $header .= '<var>'.$s.'</var>';

        if ($this->renderer->shouldRenderObjectIds()) {
            $header .= '#'.$o->getSplObjectId();
        }

        if (null !== ($file = $o->getFileName()) && null !== ($line = $o->getStartLine())) {
            $header .= ' '.$this->renderer->escape(Kint::shortenPath($file)).':'.$line;
        }

        $header = $this->renderer->renderHeaderWrapper($c, (bool) \strlen($children), $header);

        return '<dl>'.$header.$children.'</dl>';
    }
}
