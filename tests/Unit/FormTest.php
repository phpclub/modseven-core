<?php

declare(strict_types=1);

namespace Modseven\Tests\Unit;

use Modseven\Form;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Modseven\Form
 */
class FormTest extends TestCase
{
    // -------------------------------------------------------------------------
    // close
    // -------------------------------------------------------------------------

    public function testClose(): void
    {
        $this->assertSame('</form>', Form::close());
    }

    // -------------------------------------------------------------------------
    // input
    // -------------------------------------------------------------------------

    public function testInputDefaultsToText(): void
    {
        $html = Form::input('username', 'john');
        $this->assertStringContainsString('type="text"', $html);
        $this->assertStringContainsString('name="username"', $html);
        $this->assertStringContainsString('value="john"', $html);
        $this->assertStringStartsWith('<input', $html);
        $this->assertStringEndsWith(' />', $html);
    }

    public function testInputNullValueOmitted(): void
    {
        $html = Form::input('field');
        $this->assertStringNotContainsString('value=', $html);
    }

    public function testInputEscapesValue(): void
    {
        $html = Form::input('q', '<script>');
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    // -------------------------------------------------------------------------
    // hidden
    // -------------------------------------------------------------------------

    public function testHidden(): void
    {
        $html = Form::hidden('token', 'abc123');
        $this->assertStringContainsString('type="hidden"', $html);
        $this->assertStringContainsString('name="token"', $html);
        $this->assertStringContainsString('value="abc123"', $html);
    }

    // -------------------------------------------------------------------------
    // password
    // -------------------------------------------------------------------------

    public function testPassword(): void
    {
        $html = Form::password('pass', 'secret');
        $this->assertStringContainsString('type="password"', $html);
        $this->assertStringContainsString('name="pass"', $html);
        $this->assertStringContainsString('value="secret"', $html);
    }

    // -------------------------------------------------------------------------
    // file
    // -------------------------------------------------------------------------

    public function testFileHasNoValue(): void
    {
        $html = Form::file('upload');
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('name="upload"', $html);
        $this->assertStringNotContainsString('value=', $html);
    }

    // -------------------------------------------------------------------------
    // checkbox
    // -------------------------------------------------------------------------

    public function testCheckboxChecked(): void
    {
        $html = Form::checkbox('agree', '1', true);
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('checked=', $html);
    }

    public function testCheckboxUnchecked(): void
    {
        $html = Form::checkbox('agree', '1', false);
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringNotContainsString('checked', $html);
    }

    // -------------------------------------------------------------------------
    // radio
    // -------------------------------------------------------------------------

    public function testRadioChecked(): void
    {
        $html = Form::radio('color', 'red', true);
        $this->assertStringContainsString('type="radio"', $html);
        $this->assertStringContainsString('name="color"', $html);
        $this->assertStringContainsString('checked=', $html);
    }

    // -------------------------------------------------------------------------
    // textarea
    // -------------------------------------------------------------------------

    public function testTextareaBasic(): void
    {
        $html = Form::textarea('bio', 'Hello World');
        $this->assertStringStartsWith('<textarea', $html);
        $this->assertStringContainsString('name="bio"', $html);
        $this->assertStringContainsString('>Hello World</textarea>', $html);
    }

    public function testTextareaDefaultRowsCols(): void
    {
        $html = Form::textarea('notes');
        $this->assertStringContainsString('cols="50"', $html);
        $this->assertStringContainsString('rows="10"', $html);
    }

    public function testTextareaEscapesBody(): void
    {
        $html = Form::textarea('code', '<b>bold</b>');
        $this->assertStringContainsString('&lt;b&gt;', $html);
    }

    // -------------------------------------------------------------------------
    // select
    // -------------------------------------------------------------------------

    public function testSelectSimple(): void
    {
        $html = Form::select('color', ['red' => 'Red', 'blue' => 'Blue'], 'red');
        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('name="color"', $html);
        $this->assertStringContainsString('value="red"', $html);
        $this->assertStringContainsString('selected=', $html);
        $this->assertStringContainsString('value="blue"', $html);
    }

    public function testSelectMultiple(): void
    {
        $html = Form::select('colors', ['red' => 'Red', 'blue' => 'Blue'], ['red', 'blue']);
        $this->assertStringContainsString('multiple=', $html);
        $this->assertSame(2, substr_count($html, 'selected='));
    }

    public function testSelectWithOptgroup(): void
    {
        $options = [
            'Warm' => ['red' => 'Red', 'orange' => 'Orange'],
            'Cool' => ['blue' => 'Blue'],
        ];
        $html = Form::select('hue', $options);
        $this->assertStringContainsString('<optgroup', $html);
        $this->assertStringContainsString('label="Warm"', $html);
        $this->assertStringContainsString('label="Cool"', $html);
    }

    public function testSelectEmpty(): void
    {
        $html = Form::select('empty', []);
        $this->assertSame('<select name="empty"></select>', $html);
    }

    // -------------------------------------------------------------------------
    // submit
    // -------------------------------------------------------------------------

    public function testSubmit(): void
    {
        $html = Form::submit('save', 'Save Changes');
        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringContainsString('name="save"', $html);
        $this->assertStringContainsString('value="Save Changes"', $html);
    }

    // -------------------------------------------------------------------------
    // button
    // -------------------------------------------------------------------------

    public function testButton(): void
    {
        $html = Form::button('go', 'Click Me');
        $this->assertStringStartsWith('<button', $html);
        $this->assertStringContainsString('name="go"', $html);
        $this->assertStringContainsString('>Click Me</button>', $html);
    }

    public function testButtonAllowsHtmlBody(): void
    {
        $html = Form::button('go', '<span>icon</span>');
        // body is NOT escaped
        $this->assertStringContainsString('<span>icon</span>', $html);
    }

    // -------------------------------------------------------------------------
    // label
    // -------------------------------------------------------------------------

    public function testLabelWithExplicitText(): void
    {
        $html = Form::label('email', 'Email Address');
        $this->assertStringContainsString('for="email"', $html);
        $this->assertStringContainsString('>Email Address</label>', $html);
    }

    public function testLabelAutoGeneratesText(): void
    {
        $html = Form::label('first_name');
        // preg_replace('/[\W_]+/', ' ', 'first_name') = 'first name' → ucwords = 'First Name'
        $this->assertStringContainsString('>First Name</label>', $html);
        $this->assertStringContainsString('for="first_name"', $html);
    }
}
