<?php
/**
 * This file is part of the Metadata package.
 *
 * @author Daniel Schröder <daniel.schroeder@gravitymedia.de>
 */

namespace GravityMedia\Metadata\ID3v2;

use GravityMedia\Metadata\Exception\BadMethodCallException;
use GravityMedia\Metadata\ID3v2\Enum\Version;
use GravityMedia\Metadata\Metadata\ExtendedHeaderInterface;
use GravityMedia\Metadata\Metadata\FrameInterface;
use GravityMedia\Metadata\Metadata\HeaderInterface;
use GravityMedia\Metadata\Metadata\TagInterface;

/**
 * ID3v2 tag
 *
 * @package GravityMedia\Metadata\ID3v2
 */
class Tag implements TagInterface
{
    /**
     * @var HeaderInterface
     */
    protected $header;

    /**
     * @var ExtendedHeader
     */
    protected $extendedHeader;

    /**
     * @var FrameInterface[]
     */
    protected $frames;

    /**
     * Create tag object.
     *
     * @param HeaderInterface $header
     */
    public function __construct(HeaderInterface $header)
    {
        $this->header = $header;
        $this->frames = new \ArrayObject();
    }

    /**
     * Get header
     *
     * @return HeaderInterface
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Get extended header
     *
     * @return ExtendedHeader
     */
    public function getExtendedHeader()
    {
        return $this->extendedHeader;
    }

    /**
     * Set extended header
     *
     * @param ExtendedHeaderInterface $extendedHeader
     *
     * @throws BadMethodCallException An exception is thrown on ID3 v2.2 tag
     *
     * @return $this
     */
    public function setExtendedHeader(ExtendedHeaderInterface $extendedHeader)
    {
        if (!in_array($this->getVersion(), [Version::VERSION_23, Version::VERSION_24])) {
            throw new BadMethodCallException('Extended header is not supported in this version.');
        }

        $this->extendedHeader = $extendedHeader;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->header->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        // TODO: Implement getTitle() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getArtist()
    {
        // TODO: Implement getArtist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getAlbum()
    {
        // TODO: Implement getAlbum() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getYear()
    {
        // TODO: Implement getYear() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        // TODO: Implement getComment() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getTrack()
    {
        // TODO: Implement getTrack() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getGenre()
    {
        // TODO: Implement getGenre() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getFrames()
    {
        return $this->frames;
    }

    /**
     * Add frame
     *
     * @param FrameInterface $frame
     *
     * @return $this
     */
    public function addFrame(FrameInterface $frame)
    {
        $this->frames->append($frame);
        return $this;
    }
}
