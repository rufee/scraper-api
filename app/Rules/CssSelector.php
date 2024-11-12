<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Symfony\Component\CssSelector\CssSelectorConverter;

/**
 * CSS selector validator
 *
 * Validates if a passed string is a valid CSS selector
 * Validation method in css-selector is internal so instead we try/catch to get a result
 *
 */
class CssSelector implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $cssSelector = new CssSelectorConverter();
            $cssSelector->toXPath($value);
        } catch (\Exception $e) {
            $fail('Attribute :attribute must be a valid CSS selector');
        }
    }
}
