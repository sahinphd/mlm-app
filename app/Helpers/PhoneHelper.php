<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Clean and normalize a phone number into a standard canonical format.
     * For Indian numbers: standard 10-digit string (e.g., "9876543210").
     * For international numbers: "+<country_code><digits>" with no spaces (e.g., "+8801712345678").
     */
    public static function normalize(?string $phone, ?string $country = null): ?string
    {
        if ($phone === null) {
            return null;
        }

        // Remove all whitespace (including unicode, non-breaking, zero-width)
        $clean = preg_replace('/[\s\x{00a0}\x{200B}-\x{200D}\x{FEFF}]+/u', '', trim($phone));
        if ($clean === '') {
            return '';
        }

        $cleanCountry = null;
        if (!empty($country)) {
            $cDigits = preg_replace('/[^0-9]/', '', $country);
            if ($cDigits !== '') {
                $cleanCountry = '+' . $cDigits;
            }
        }

        $hasLeadingPlus = str_starts_with($clean, '+');
        $digits = preg_replace('/[^0-9]/', '', $clean);

        if ($digits === '') {
            return '';
        }

        // Determine if this is an Indian phone number
        $isIndia = false;
        if ($cleanCountry === '+91') {
            $isIndia = true;
        } elseif ($cleanCountry === null) {
            if ($hasLeadingPlus && str_starts_with($digits, '91') && strlen($digits) === 12) {
                // e.g. +91 9876543210
                $isIndia = true;
            } elseif (!$hasLeadingPlus && strlen($digits) === 10) {
                // e.g. 9876543210
                $isIndia = true;
            } elseif (!$hasLeadingPlus && strlen($digits) === 11 && str_starts_with($digits, '0')) {
                // e.g. 09876543210
                $isIndia = true;
            } elseif (!$hasLeadingPlus && strlen($digits) === 12 && str_starts_with($digits, '91')) {
                // e.g. 919876543210
                $isIndia = true;
            }
        }

        if ($isIndia) {
            // Strip leading 91 (if 12 digits) or leading 0 (if 11 digits)
            if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
                $digits = substr($digits, 2);
            } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
                $digits = substr($digits, 1);
            }
            return $digits;
        }

        // International phone number
        if ($cleanCountry !== null) {
            $countryDigits = ltrim($cleanCountry, '+');
            if (str_starts_with($digits, $countryDigits)) {
                return '+' . $digits;
            }
            return $cleanCountry . $digits;
        }

        if ($hasLeadingPlus) {
            return '+' . $digits;
        }

        return $digits;
    }

    /**
     * Check if a normalized phone number is structurally valid.
     */
    public static function isValid(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }

        // Standard Indian mobile number: 10 digits
        if (preg_match('/^[0-9]{10}$/', $phone)) {
            return true;
        }

        // International phone number: + followed by 7 to 15 digits
        if (preg_match('/^\+[1-9][0-9]{6,14}$/', $phone)) {
            return true;
        }

        return false;
    }

    /**
     * Get all possible variants of a phone number to search in the database.
     * This handles records stored as 10-digit, +91..., 91..., with or without spaces.
     */
    public static function getPossibleVariants(?string $input): array
    {
        if (empty($input)) {
            return [];
        }

        $inputTrimmed = trim($input);
        $inputNoSpaces = preg_replace('/[\s\x{00a0}\x{200B}-\x{200D}\x{FEFF}]+/u', '', $inputTrimmed);
        $digits = preg_replace('/[^0-9]/', '', $inputTrimmed);

        $variants = [
            $inputTrimmed,
            $inputNoSpaces,
        ];

        if ($digits !== '') {
            $variants[] = $digits;
            $variants[] = '+' . $digits;

            // If it could be an Indian number (has at least 10 digits)
            if (strlen($digits) >= 10) {
                $last10 = substr($digits, -10);
                $variants[] = $last10;
                $variants[] = '+91' . $last10;
                $variants[] = '+91 ' . $last10;
                $variants[] = '91' . $last10;
                $variants[] = '91 ' . $last10;
                $variants[] = '0' . $last10;
            }
        }

        // Remove empty strings and duplicates
        return array_values(array_unique(array_filter($variants, fn($v) => !empty($v))));
    }

    /**
     * Format phone number for clean UI display.
     */
    public static function formatForDisplay(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 10) {
            return '+91 ' . substr($digits, 0, 5) . ' ' . substr($digits, 5);
        }

        return $phone;
    }
}
