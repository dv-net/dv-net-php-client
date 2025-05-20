<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\SimpleHttp;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    /** @var resource|null  */
    private $stream;
    private bool $seekable;
    private bool $readable;
    private bool $writable;
    private ?int $size;
    /** @var array<string, mixed>  */
    private array $metadata;

    /**
     * @param resource|false $stream
     */
    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new RuntimeException('Stream must be a resource');
        }

        $this->stream   = $stream;
        $this->metadata = stream_get_meta_data($this->stream);
        $this->seekable = $this->metadata['seekable'];
        $this->readable = str_contains($this->metadata['mode'], 'r') || str_contains($this->metadata['mode'], '+');
        $this->writable = str_contains($this->metadata['mode'], 'w') || str_contains($this->metadata['mode'], 'a') || str_contains($this->metadata['mode'], '+');
        $this->size     = null;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        if ($this->isSeekable()) {
            $this->rewind();
        }

        return $this->getContents();
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size     = null;
        $this->seekable = false;
        $this->readable = false;
        $this->writable = false;
        $this->metadata = [];

        return $result;
    }

    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        // Clear the stat cache if the stream has been modified
        clearstatcache(true, $this->metadata['uri']);
        $stats = fstat($this->stream);

        if (isset($stats['size'])) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return null;
    }

    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        $result = ftell($this->stream);

        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    public function eof(): bool
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        return feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position ' . $offset);
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write(string $string): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->writable) {
            throw new RuntimeException('Stream is not writable');
        }

        $result = fwrite($this->stream, $string);

        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }

        $this->size = null;

        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read(int $length): string
    {
        if ($length <= 0) {
            throw new RuntimeException('The length of the stream cannot be negative.');
        }
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = fread($this->stream, $length);

        if ($result === false) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $result;
    }

    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = stream_get_contents($this->stream);

        if ($result === false) {
            throw new RuntimeException('Unable to read stream contents');
        }

        return $result;
    }

    public function getMetadata(?string $key = null)
    {
        if (!isset($this->stream)) {
            return $key ?? [];
        }

        if ($key === null) {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? null;
    }
}
