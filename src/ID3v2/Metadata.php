<?php
/**
 * This file is part of the Metadata project.
 *
 * @author Daniel Schröder <daniel.schroeder@gravitymedia.de>
 */

namespace GravityMedia\Metadata\ID3v2;

use GravityMedia\Metadata\Exception\InvalidArgumentException;
use GravityMedia\Metadata\Exception\RuntimeException;
use GravityMedia\Metadata\ID3v2\Filter\CompressionFilter;
use GravityMedia\Metadata\ID3v2\Filter\UnsynchronisationFilter;
use GravityMedia\Metadata\ID3v2\Flag\FrameFlag;
use GravityMedia\Metadata\ID3v2\Flag\HeaderFlag;
use GravityMedia\Metadata\ID3v2\Frame\CommentFrame;
use GravityMedia\Metadata\ID3v2\Frame\PictureFrame;
use GravityMedia\Metadata\ID3v2\Frame\TextFrame;
use GravityMedia\Metadata\ID3v2\Reader\ExtendedHeaderReader;
use GravityMedia\Metadata\ID3v2\Reader\FrameHeaderReader;
use GravityMedia\Metadata\ID3v2\Reader\HeaderReader;
use GravityMedia\Metadata\ID3v2\Reader\LanguageTextFrameReader;
use GravityMedia\Metadata\ID3v2\Reader\PictureFrameReader;
use GravityMedia\Metadata\ID3v2\Reader\TextFrameReader;
use GravityMedia\Metadata\ID3v2\Writer\FrameHeaderWriter;
use GravityMedia\Stream\ByteOrder;
use GravityMedia\Stream\Stream;

/**
 * ID3v2 metadata class.
 *
 * @package GravityMedia\Metadata\ID3v2
 */
class Metadata
{
    /**
     * @var Stream
     */
    private $stream;

    /**
     * @var CompressionFilter
     */
    private $compressionFilter;

    /**
     * @var UnsynchronisationFilter
     */
    private $unsynchronisationFilter;

    /**
     * Create ID3v2 metadata object from resource.
     *
     * @param resource $resource
     *
     * @throws InvalidArgumentException An exception will be thrown for invalid resource arguments.
     *
     * @return static
     */
    public static function fromResource($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Invalid resource');
        }

        $stream = Stream::fromResource($resource);
        $stream->setByteOrder(ByteOrder::BIG_ENDIAN);

        $metadata = new static();
        $metadata->stream = $stream;
        $metadata->compressionFilter = new CompressionFilter();
        $metadata->unsynchronisationFilter = new UnsynchronisationFilter();

        return $metadata;
    }

    /**
     * Returns whether ID3v2 metadata exists.
     *
     * @return bool
     */
    public function exists()
    {
        if ($this->stream->getSize() < 10) {
            return false;
        }

        $this->stream->seek(0);

        return 'ID3' === $this->stream->read(3);
    }

    /**
     * Strip ID3v2 metadata.
     *
     * @return $this
     */
    public function strip()
    {
        // TODO: implement

        return $this;
    }

    /**
     * Read ID3v2 version.
     *
     * @throws RuntimeException An exception is thrown on invalid versions.
     *
     * @return int
     */
    protected function readVersion()
    {
        $this->stream->seek(3);

        $version = $this->stream->readUInt8();

        if (2 === $version) {
            return Version::VERSION_22;
        }

        if (3 === $version) {
            return Version::VERSION_23;
        }

        if (4 === $version) {
            return Version::VERSION_24;
        }

        throw new RuntimeException('Invalid version.');
    }

    /**
     * Create readable stream from data.
     *
     * @param string $data
     *
     * @return Stream
     */
    protected function createReadableStreamFromData($data)
    {
        $filename = tempnam(sys_get_temp_dir(), 'php');
        file_put_contents($filename, $data);

        $stream = Stream::fromResource(fopen($filename, 'r'));
        $stream->setByteOrder(ByteOrder::BIG_ENDIAN);

        return $stream;
    }

    /**
     * Read ID3v2 tag.
     *
     * @return null|Tag
     */
    public function read()
    {
        if (!$this->exists()) {
            return null;
        }

        $version = $this->readVersion();
        $tag = new Tag($version);

        $this->stream->seek(4);
        $headerReader = new HeaderReader($this->stream, $version);

        $tag->setRevision($headerReader->getRevision());
        $length = $headerReader->getSize();

        $this->stream->seek(10);
        $data = $this->stream->read($length);
        if ($headerReader->isFlagEnabled(HeaderFlag::FLAG_COMPRESSION)) {
            $data = $this->compressionFilter->decode($data);
        }
        if ($headerReader->isFlagEnabled(HeaderFlag::FLAG_UNSYNCHRONISATION)) {
            $data = $this->unsynchronisationFilter->decode($data);
        }

        $tagStream = $this->createReadableStreamFromData($data);
        $tagLength = $tagStream->getSize();

        if ($headerReader->isFlagEnabled(HeaderFlag::FLAG_EXTENDED_HEADER)) {
            $extendedHeaderReader = new ExtendedHeaderReader($tagStream, $version);
            $tagLength -= $extendedHeaderReader->getSize();

            $tag->setPadding($extendedHeaderReader->getPadding());
            $tag->setCrc32($extendedHeaderReader->getCrc32());
            $tag->setRestrictions($extendedHeaderReader->getRestrictions());
        }

        if ($headerReader->isFlagEnabled(HeaderFlag::FLAG_FOOTER_PRESENT)) {
            // TODO: Read footer metadata.

            $tagLength -= 10;
        }

        while ($tagLength > 0) {
            $frameHeaderReader = new FrameHeaderReader($tagStream, $version);

            $frameName = $frameHeaderReader->getName();
            $frameLength = $frameHeaderReader->getSize();
            $tagLength -= $frameLength;

            if ('' === $frameName || 0 === $frameLength) {
                break;
            }

            $data = $tagStream->read($frameHeaderReader->getDataLength());
            if ($frameHeaderReader->isFlagEnabled(FrameFlag::FLAG_COMPRESSION)) {
                $data = $this->compressionFilter->decode($data);
            }
            if ($frameHeaderReader->isFlagEnabled(FrameFlag::FLAG_UNSYNCHRONISATION)) {
                $data = $this->unsynchronisationFilter->decode($data);
            }

            $frameStream = $this->createReadableStreamFromData($data);

            if ('UFID' === $frameName) {
                $frame = new Frame();
                $frame->setName($frameName);
                // TODO: Read unique file identifier.
            } elseif ('T' === substr($frameName, 0, 1)) {
                $frameReader = new TextFrameReader($frameStream);

                $frame = new TextFrame();
                $frame->setName($frameName);
                $frame->setText($frameReader->getText());

                if ('TXXX' === $frameName) {
                    // TODO: Read user defined text frame.
                }
            } elseif ('W' === substr($frameName, 0, 1)) {
                $frame = new Frame();
                $frame->setName($frameName);
                // TODO: Read URL link frame.
                if ('WXXX' === $frameName) {
                    // TODO: Read user defined URL link frame.
                }
            } elseif ('APIC' === $frameName) {
                $frameReader = new PictureFrameReader($frameStream);

                $frame = new PictureFrame();
                $frame->setName($frameName);
                $frame->setMimeType($frameReader->getMimeType());
                $frame->setType($frameReader->getType());
                $frame->setDescription($frameReader->getDescription());
                $frame->setData($frameReader->getData());
            } elseif ('COMM' === $frameName) {
                $frameReader = new LanguageTextFrameReader($frameStream);
                $text = $frameReader->getText();
                $description = array_shift($text);

                $frame = new CommentFrame();
                $frame->setName($frameName);
                $frame->setLanguage($frameReader->getLanguage());
                $frame->setDescription($description);
                $frame->setText($text);
            } else {
                $frame = new Frame();
                $frame->setName($frameName);
            }

            $tag->addFrame($frame);
        }

        return $tag;
    }

    /**
     * Write ID3v2 tag.
     *
     * @param Tag $tag The tag to write.
     *
     * @return $this
     */
    public function write(Tag $tag)
    {
        $stream = Stream::fromResource(fopen('php://temp', 'w'));
        $stream->setByteOrder(ByteOrder::BIG_ENDIAN);

        $stream->write('ID3');
        $stream->writeUInt8($tag->getVersion());
        $stream->writeUInt8($tag->getRevision());

        $tagStream = Stream::fromResource(fopen('php://temp', 'w'));
        $tagStream->setByteOrder(ByteOrder::BIG_ENDIAN);

        foreach ($tag->getFrames() as $frame) {
            $frameHeaderWriter = new FrameHeaderWriter($tagStream, $tag->getVersion());
            $frameHeaderWriter->setName($frame->getName());

        }

        return $this;
    }
}
