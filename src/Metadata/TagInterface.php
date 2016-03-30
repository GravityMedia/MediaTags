<?php
/**
 * This file is part of the Metadata package.
 *
 * @author Daniel Schröder <daniel.schroeder@gravitymedia.de>
 */

namespace GravityMedia\Metadata\Metadata;

/**
 * Metadata tag interface.
 *
 * @package GravityMedia\Metadata\Metadata
 */
interface TagInterface
{
    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get artist.
     *
     * @return string
     */
    public function getArtist();

    /**
     * Get album.
     *
     * @return string
     */
    public function getAlbum();

    /**
     * Get year.
     *
     * @return int
     */
    public function getYear();

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment();

    /**
     * Get track.
     *
     * @return int
     */
    public function getTrack();

    /**
     * Get genre.
     *
     * @return string
     */
    public function getGenre();
}
