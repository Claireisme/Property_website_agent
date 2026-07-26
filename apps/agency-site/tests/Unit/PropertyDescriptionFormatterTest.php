<?php

namespace Tests\Unit;

use App\Support\PropertyDescriptionFormatter;
use Tests\TestCase;

class PropertyDescriptionFormatterTest extends TestCase
{
    public function test_it_preserves_simple_markdown_and_line_breaks_when_rendering(): void
    {
        $html = PropertyDescriptionFormatter::toHtml("First line\nSecond line with **bold**");

        $this->assertStringContainsString('First line<br', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
    }

    public function test_it_strips_word_style_html_from_pasted_description(): void
    {
        $cleaned = PropertyDescriptionFormatter::cleanMarkdownInput(
            '<p style="color: red; font-family: Comic Sans MS;">Freshly painted</p><p><span style="font-size: 48px;">Close to schools</span></p>'
        );

        $this->assertSame("Freshly painted\nClose to schools", $cleaned);

        $html = PropertyDescriptionFormatter::toHtml($cleaned);

        $this->assertStringNotContainsString('style=', $html);
        $this->assertStringNotContainsString('<span', $html);
        $this->assertStringContainsString('Freshly painted<br', $html);
    }
}
