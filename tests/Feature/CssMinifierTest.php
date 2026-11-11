<?php

namespace Tests\Feature;

use App\Support\CssMinifier;
use Tests\TestCase;

class CssMinifierTest extends TestCase
{
    public function test_it_strips_comments_and_whitespace_but_keeps_values_intact(): void
    {
        $css = "/* It's a comment with an apostrophe */\n.a ,\n.b {\n    width: calc(100% - 8px);\n    content: \" ; { } \";\n}\n";

        $this->assertSame('.a,.b{width: calc(100% - 8px);content: " ; { } "}', CssMinifier::minify($css));
    }

    public function test_it_never_returns_an_empty_stylesheet(): void
    {
        $this->assertSame('', CssMinifier::minify(''));
        $this->assertNotSame('', CssMinifier::minify(".a { color: red; }"));
    }
}
