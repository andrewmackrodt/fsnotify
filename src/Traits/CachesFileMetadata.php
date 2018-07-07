<?php

namespace Denimsoft\FsNotify\Traits;

use SplFileInfo;

trait CachesFileMetadata
{
    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @param SplFileInfo|string $file
     * @param array|null $oldFileMetadata
     *
     * @return array
     */
    protected function getFileMetadata($file, &$oldFileMetadata = null): array
    {
        if (!$file instanceof SplFileInfo) {
            $file = new SplFileInfo($file);
        }

        $oldFileMetadata = $this->metadata[$file->getPathname()] ?? null;

        try {
            $fileMetadata = [
                'filetype'    => $file->isDir() ? 'directory' : 'file',
                'inode'       => $file->getInode(),
                'size'        => $file->getSize(),
                'modified'    => $file->getMTime(),
                'owner'       => $file->getOwner(),
                'group'       => $file->getGroup(),
                'permissions' => $file->getPerms(),
            ];
        } catch (\RuntimeException $e) {
            if (stripos($e->getMessage(), 'stat failed') === false) {
                throw $e;
            }

            $fileMetadata = [];
        }

        return $this->metadata[$file->getPathname()] = $fileMetadata;
    }
}
