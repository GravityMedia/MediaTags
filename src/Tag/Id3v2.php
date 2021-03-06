<?php
/**
 * This file is part of the metadata package
 *
 * @author Daniel Schröder <daniel.schroeder@gravitymedia.de>
 */

namespace GravityMedia\Metadata\Tag;

use GravityMedia\Metadata\Feature\Picture;
use GravityMedia\Metadata\GetId3;
use Zend\Stdlib\Hydrator\ClassMethods;

/**
 * ID3 v2 tag object
 *
 * @package GravityMedia\Metadata\Tag
 */
class Id3v2 extends AbstractTag
{
    /**
     * @var array
     */
    public static $FRAMENAMES = array(
        'TIT2', // title/songname/content description
        'TPE1', // artist(s)/lead performer(s)/soloist(s)
        'TALB', // album/movie/show title
        'TYER', // year
        'COMM', // comments
        'TRCK', // track number/position in set
        'TCON', // genre/content type
        'TPE2', // band/orchestra/accompaniment
        'TIT1', // works/content group description
        'TCOM', // composer(s)
        'TOPE', // original artist(s)/performer(s)
        'TPOS', // disc number/part of a set
        'APIC'  // picture/attached picture
    );

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $artist;

    /**
     * @var string
     */
    protected $album;

    /**
     * @var int
     */
    protected $year;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var int
     */
    protected $track;

    /**
     * @var int
     */
    protected $trackCount;

    /**
     * @var string
     */
    protected $genre;

    /**
     * @var string
     */
    protected $band;

    /**
     * @var string
     */
    protected $works;

    /**
     * @var string
     */
    protected $composer;

    /**
     * @var string
     */
    protected $originalArtist;

    /**
     * @var int
     */
    protected $disc;

    /**
     * @var int
     */
    protected $discCount;

    /**
     * @var \GravityMedia\Metadata\Feature\Picture
     */
    protected $picture;

    /**
     * Save tag
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function save()
    {
        $hydrator = new ClassMethods();
        $properties = $hydrator->extract($this);
        $data = array();

        unset($properties['audio_properties']);
        foreach ($properties as $name => $value) {
            if (null === $value) {
                continue;
            }
            switch ($name) {
                case 'track':
                    if (null !== $properties['track_count']) {
                        $value .= '/' . $properties['track_count'];
                    }
                    $data['track'] = array($value);
                    break;
                case 'works':
                    $data['content_group_description'] = array($value);
                    break;
                case 'disc':
                    if (null !== $properties['disc_count']) {
                        $value .= '/' . $properties['disc_count'];
                    }
                    $data['part_of_a_set'] = array($value);
                    break;
                case 'picture':
                    /** @var \GravityMedia\Metadata\Feature\Picture $value */
                    $data['attached_picture'] = array(array(
                        'data' => $value->getData(),
                        'mime' => $value->getMime(),
                        'picturetypeid' => array_search($value->getPictureType(), GetId3::getId3v2PictureTypes()),
                        'description' => $value->getDescription()
                    ));
                    break;
                default:
                    $data[$name] = array($value);
                    break;
            }
        }

        return $this->writer->write($data);
    }

    /**
     * Remove tag
     *
     * @return $this
     */
    public function remove()
    {
        return $this->writer->write();
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set artist
     *
     * @param string $artist
     *
     * @return $this
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;
        return $this;
    }

    /**
     * Get artist
     *
     * @return string
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Set album
     *
     * @param string $album
     *
     * @throws \BadMethodCallException
     * @return $this
     */
    public function setAlbum($album)
    {
        $this->album = $album;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Set year
     *
     * @param int $year
     *
     * @return $this
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * Get year
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @throws \BadMethodCallException
     * @return $this
     */
    public function setComment($comment)
    {

        $this->comment = $comment;
        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set track number
     *
     * @param int $track
     *
     * @throws \BadMethodCallException
     * @return $this
     */
    public function setTrack($track)
    {
        $this->track = $track;
        return $this;
    }

    /**
     * Get track number
     *
     * @return int
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * Set track count
     *
     * @param int $trackCount
     *
     * @throws \BadMethodCallException
     * @return $this
     */
    public function setTrackCount($trackCount)
    {
        if (null !== $this->track) {
            $this->trackCount = $trackCount;
            return $this;
        }
        throw new \BadMethodCallException('The track must be set before the track count');
    }

    /**
     * Get track count
     *
     * @return int
     */
    public function getTrackCount()
    {
        return $this->trackCount;
    }

    /**
     * Set genre
     *
     * @param string $genre
     *
     * @return $this
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
        return $this;
    }

    /**
     * Get genre
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set band
     *
     * @param string $band
     *
     * @return $this
     */
    public function setBand($band)
    {
        $this->band = $band;
        return $this;
    }

    /**
     * Get band
     *
     * @return string
     */
    public function getBand()
    {
        return $this->band;
    }

    /**
     * Set works
     *
     * @param string $works
     *
     * @return $this
     */
    public function setWorks($works)
    {
        $this->works = $works;
        return $this;
    }

    /**
     * Get works
     *
     * @return string
     */
    public function getWorks()
    {
        return $this->works;
    }

    /**
     * Set composer
     *
     * @param string $composer
     *
     * @return $this
     */
    public function setComposer($composer)
    {
        $this->composer = $composer;
        return $this;
    }

    /**
     * Get composer
     *
     * @return string
     */
    public function getComposer()
    {
        return $this->composer;
    }

    /**
     * Set original artist
     *
     * @param string $originalArtist
     *
     * @return $this
     */
    public function setOriginalArtist($originalArtist)
    {
        $this->originalArtist = $originalArtist;
        return $this;
    }

    /**
     * Get original artist
     *
     * @return string
     */
    public function getOriginalArtist()
    {
        return $this->originalArtist;
    }

    /**
     * Set disc
     *
     * @param int $disc
     *
     * @return $this
     */
    public function setDisc($disc)
    {
        $this->disc = $disc;
        return $this;
    }

    /**
     * Get disc
     *
     * @return int
     */
    public function getDisc()
    {
        return $this->disc;
    }

    /**
     * Set disc count
     *
     * @param int $discCount
     *
     * @throws \BadMethodCallException
     * @return $this
     */
    public function setDiscCount($discCount)
    {
        if (null !== $this->disc) {
            $this->discCount = $discCount;
            return $this;
        }
        throw new \BadMethodCallException('The disc must be set before the disc count');
    }

    /**
     * Get disc count
     *
     * @return int
     */
    public function getDiscCount()
    {
        return $this->discCount;
    }

    /**
     * Set picture
     *
     * @param \GravityMedia\Metadata\Feature\Picture $picture
     *
     * @return $this
     */
    public function setPicture(Picture $picture)
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * Get picture
     *
     * @return \GravityMedia\Metadata\Feature\Picture
     */
    public function getPicture()
    {
        return $this->picture;
    }
}
