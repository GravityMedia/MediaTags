<?php
/**
 * This file is part of the Metadata project.
 *
 * @author Daniel Schröder <daniel.schroeder@gravitymedia.de>
 */

namespace GravityMedia\Metadata\ID3v2\Filter;

/**
 * Unsynchronisation filter class.
 *
 * @package GravityMedia\Metadata\ID3v2\Filter
 */
class UnsynchronisationFilter
{
    /**
     * Encode data.
     *
     * @param string $data
     *
     * @return string
     */
    public function encode($data)
    {
        return preg_replace('/\xff(?=[\xe0-\xff])/', "\xff\x00", preg_replace('/\xff\x00/', "\xff\x00\x00", $data));
    }

    /**
     * Decode data.
     *
     * @param string $data
     *
     * @return string
     */
    public function decode($data)
    {
        return preg_replace('/\xff\x00\x00/', "\xff\x00", preg_replace('/\xff\x00(?=[\xe0-\xff])/', "\xff", $data));
    }
}
