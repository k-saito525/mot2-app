<?php

namespace Tests\Unit\Models;

use App\Models\Topic;
use PHPUnit\Framework\TestCase;

class TopicTest extends TestCase
{
    public function test_makeLink_returns_empty_for_empty_string(): void
    {
        $this->assertSame('', Topic::makeLink(''));
    }

    public function test_makeLink_returns_plain_text_unchanged(): void
    {
        $this->assertSame('URLのないテキスト', Topic::makeLink('URLのないテキスト'));
    }

    public function test_makeLink_converts_http_url_to_anchor(): void
    {
        $result = Topic::makeLink('http://example.com');

        $this->assertSame(
            '<a class="content-link" href="http://example.com">http://example.com</a>',
            $result
        );
    }

    public function test_makeLink_converts_https_url_to_anchor(): void
    {
        $result = Topic::makeLink('https://example.com');

        $this->assertSame(
            '<a class="content-link" href="https://example.com">https://example.com</a>',
            $result
        );
    }

    public function test_makeLink_converts_url_embedded_in_text(): void
    {
        $result = Topic::makeLink('詳細は https://example.com を参照してください');

        $this->assertStringContainsString(
            '<a class="content-link" href="https://example.com">https://example.com</a>',
            $result
        );
        $this->assertStringContainsString('詳細は', $result);
        $this->assertStringContainsString('を参照してください', $result);
    }

    public function test_makeLink_escapes_html_special_characters(): void
    {
        $result = Topic::makeLink('<script>alert(1)</script>');

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function test_makeLink_escapes_html_and_converts_url(): void
    {
        $result = Topic::makeLink('<b>見てください</b> https://example.com');

        $this->assertStringNotContainsString('<b>', $result);
        $this->assertStringContainsString('&lt;b&gt;', $result);
        $this->assertStringContainsString('<a class="content-link" href="https://example.com">', $result);
    }
}
