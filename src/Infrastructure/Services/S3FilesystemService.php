<?php
declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Services\FileService\FileServiceException;
use App\Application\Services\FileService\FileServiceInterface;
use App\Application\Services\FileService\S3FilesystemServiceInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

class S3FilesystemService implements S3FilesystemServiceInterface
{
    /**
     * @var S3Client
     */
    private S3Client $storage;

    /**
     * S3FilesystemService constructor.
     * @param S3Client $s3
     */
    public function __construct(S3Client $s3)
    {
        $this->storage = $s3;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function delete(string $identifier): bool
    {
        $result = $this->storage->deleteObject([
            'Bucket' => 'comics-reader',
            'Key' => $identifier
        ]);

        return $result->get('DeleteMarker');
    }

    /**
     * @param string $identifier
     * @param UploadedFileInterface $uploadedFile
     * @return string
     * @throws FileServiceException
     */
    public function put(string $identifier, UploadedFileInterface $uploadedFile): string
    {
        try {
            $result = $this->storage->putObject([
                /**
                 * Что ж, вероятно ты смотришь на эту строчку и думаешь, ну вот какого хуя это захардкожено,
                 * так у меня есть ответ на один аккаунт можно хранить только 100 бакетов (попросишь расширят)
                 * но у бакетов нет никаких ограничений ни на кол-во файлов, ни на размер бакета, только на размер файла
                 * и это ограничение в пять, сука, терабайт, так что ты не пострадаешь от захардкоженности этой строчки
                 */
                'Bucket' => 'supercomics',
                'ACL' => 'private',
                'Key' => $identifier,
                'ContentType' => $uploadedFile->getClientMediaType(),
                'Body' => $uploadedFile->getStream(),
                'version'
            ]);
        } catch (S3Exception $e) {
            throw new FileServiceException($e->getMessage(), $e->getCode());
        }

        return $result->get('ObjectURL');
    }

    /**
     * @param string $bucket
     * @param string $identifier
     * @return UploadedFileInterface
     */
    public function get(string $bucket, string $identifier): UploadedFileInterface
    {
        $s3file = $this->storage->getObject([
            'Key' => $identifier,
            'Bucket' => $bucket
        ]);

        return new UploadedFile(
            $s3file->get('Body'),
            $identifier,
            $s3file->get('@metadata')['headers']['content-type'],
            $s3file->get('ContentLength')
        );
    }
}
