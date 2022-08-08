<?php

namespace Norgul\Xmpp\Xml;

use Norgul\Xmpp\Exceptions\StreamError;

trait Xml
{
    /**
     * Opening tag for starting a XMPP stream exchange.
     */
    public static function openXmlStream(string $host): string
    {
        $xmlOpen = "<?xml version='1.0' encoding='UTF-8'?>";
        $to = "to='{$host}'";
        $stream = "xmlns:stream='http://etherx.jabber.org/streams'";
        $client = "xmlns='jabber:client'";
        $version = "version='1.0'";

        return "{$xmlOpen}<stream:stream $to $stream $client $version>";
    }

    /**
     * Closing tag for one XMPP stream session.
     */
    public static function closeXmlStream(): string
    {
        return '</stream:stream>';
    }

    public static function quote(string $input): string
    {
        return htmlspecialchars($input, ENT_XML1, 'utf-8');
    }

    public static function parseTag(string $rawResponse, string $tag): array
    {
        preg_match_all("#(<$tag.*?>.*?<\/$tag>)#si", $rawResponse, $matched);

        return count($matched) <= 1 ? [] : array_map(function ($match) {
            return @simplexml_load_string($match);
        }, $matched[1]);
    }

    public static function parseFeatures(string $xml): string
    {
        return self::matchInsideOfTag($xml, 'stream:features');
    }

    public static function isTlsSupported(string $xml): bool
    {
        $matchTag = self::matchCompleteTag($xml, 'starttls');

        return !empty($matchTag);
    }

    public static function isTlsRequired(string $xml): bool
    {
        if (!self::isTlsSupported($xml)) {
            return false;
        }

        $tls = self::matchCompleteTag($xml, 'starttls');
        preg_match('#required#', $tls, $match);

        return count($match) > 0;
    }

    public static function matchCompleteTag(string $xml, string $tag): string
    {
        $match = self::matchTag($xml, $tag);

        return is_array($match) && count($match) > 0 ? $match[0] : '';
    }

    public static function matchInsideOfTag(string $xml, string $tag): string
    {
        $match = self::matchTag($xml, $tag);

        return is_array($match) && count($match) > 1 ? $match[1] : '';
    }

    private static function matchTag(string $xml, string $tag): array
    {
        preg_match("#<$tag.*?>(.*)<\/$tag>#", $xml, $match);

        return count($match) < 1 ? [] : $match;
    }

    public static function canProceed(string $xml): bool
    {
        preg_match("#<proceed xmlns=[\'|\"]urn:ietf:params:xml:ns:xmpp-tls[\'|\"]\/>#", $xml, $match);

        return count($match) > 0;
    }

    public static function supportedAuthMethods(string $xml): string
    {
        preg_match_all("#<mechanism>(.*?)<\/mechanism>#", $xml, $match);

        return count($match) < 1 ? '' : $match[1];
    }

    public static function roster(string $xml): string
    {
        preg_match_all("#<iq.*?type=[\'|\"]result[\'|\"]>(.*?)<\/iq>#", $xml, $match);

        return count($match) < 1 ? '' : $match[1];
    }

    /**
     * @throws StreamError
     */
    public static function checkForUnrecoverableErrors(string $response): void
    {
        preg_match_all("#<stream:error>(<(.*?) (.*?)\/>)<\/stream:error>#", $response, $streamErrors);

        if ((!empty($streamErrors[0])) && count($streamErrors[2]) > 0) {
            throw new StreamError($streamErrors[2][0]);
        }
    }
}
