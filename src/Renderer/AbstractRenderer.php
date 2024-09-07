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

namespace Kint\Renderer;

/**
 * @psalm-type PluginMap = array<string, class-string>
 *
 * I'd like to have PluginMap<T> but can't:
 * Psalm bug #4308
 */
abstract class AbstractRenderer implements ConstructableRendererInterface
{
    public const SORT_NONE = 0;
    public const SORT_VISIBILITY = 1;
    public const SORT_FULL = 2;

    public static ?string $js_nonce = null;
    public static ?string $css_nonce = null;

    public bool $show_trace = true;
    public bool $render_spl_ids = true;
    protected array $call_info = [];
    protected array $statics = [];

    public function __construct()
    {
    }

    public function shouldRenderObjectIds(): bool
    {
        return $this->render_spl_ids;
    }

    public function setCallInfo(array $info): void
    {
        if (!\is_array($info['modifiers'] ?? null)) {
            $info['modifiers'] = [];
        }

        if (!\is_array($info['trace'] ?? null)) {
            $info['trace'] = [];
        }

        $this->call_info = [
            'params' => $info['params'] ?? null,
            'modifiers' => $info['modifiers'],
            'callee' => $info['callee'] ?? null,
            'caller' => $info['caller'] ?? null,
            'trace' => $info['trace'],
        ];
    }

    public function getCallInfo(): array
    {
        return $this->call_info;
    }

    public function setStatics(array $statics): void
    {
        $this->statics = $statics;
        $this->show_trace = !empty($statics['display_called_from']);
    }

    public function getStatics(): array
    {
        return $this->statics;
    }

    public function filterParserPlugins(array $plugins): array
    {
        return $plugins;
    }

    public function preRender(): string
    {
        return '';
    }

    public function postRender(): string
    {
        return '';
    }
}
