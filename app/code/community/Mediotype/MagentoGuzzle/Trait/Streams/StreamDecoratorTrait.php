<?php
/**
 * Stream decorator trait
 * @property Mediotype_MagentoGuzzle_Model_Streams_StreamInterface stream
 */
trait Mediotype_MagentoGuzzle_Trait_Streams_StreamDecoratorTrait
{
    /**
     * @param Mediotype_MagentoGuzzle_Model_Streams_StreamInterface $stream Stream to decorate
     */
    public function __construct(Mediotype_MagentoGuzzle_Model_Streams_StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Magic method used to create a new stream if streams are not added in
     * the constructor of a decorator (e.g., LazyOpenStream).
     */
    public function __get($name)
    {
        if ($name == 'stream') {
            $this->stream = $this->createStream();
            return $this->stream;
        }

        throw new UnexpectedValueException("$name not found on class");
    }

    public function __toString()
    {
        try {
            $this->seek(0);
            return $this->getContents();
        } catch (Exception $e) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            trigger_error('Mediotype_MagentoGuzzle_Trait_Streams_StreamDecoratorTrait::__toString exception: '
                . (string) $e, E_USER_ERROR);
            return '';
        }
    }

    public function getContents($maxLength = -1)
    {
        return Mediotype_MagentoGuzzle_Model_Streams_Utils::copyToString($this, $maxLength);
    }

    /**
     * Allow decorators to implement custom methods
     *
     * @param string $method Missing method name
     * @param array  $args   Method arguments
     *
     * @return mixed
     */
    public function __call($method, array $args)
    {
        $result = call_user_func_array(array($this->stream, $method), $args);

        // Always return the wrapped object if the result is a return $this
        return $result === $this->stream ? $this : $result;
    }

    public function close()
    {
        $this->stream->close();
    }

    public function getMetadata($key = null)
    {
        return $this->stream instanceof Mediotype_MagentoGuzzle_Model_Streams_MetadataStreamInterface
            ? $this->stream->getMetadata($key)
            : null;
    }

    public function detach()
    {
        return $this->stream->detach();
    }

    public function getSize()
    {
        return $this->stream->getSize();
    }

    public function eof()
    {
        return $this->stream->eof();
    }

    public function tell()
    {
        return $this->stream->tell();
    }

    public function isReadable()
    {
        return $this->stream->isReadable();
    }

    public function isWritable()
    {
        return $this->stream->isWritable();
    }

    public function isSeekable()
    {
        return $this->stream->isSeekable();
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->stream->seek($offset, $whence);
    }

    public function read($length)
    {
        return $this->stream->read($length);
    }

    public function write($string)
    {
        return $this->stream->write($string);
    }

    public function flush()
    {
        return $this->stream->flush();
    }

    /**
     * Implement in subclasses to dynamically create streams when requested.
     *
     * @return Mediotype_MagentoGuzzle_Model_Streams_StreamInterface
     * @throws BadMethodCallException
     */
    protected function createStream()
    {
        throw new BadMethodCallException('createStream() not implemented in '
            . get_class($this));
    }
}
